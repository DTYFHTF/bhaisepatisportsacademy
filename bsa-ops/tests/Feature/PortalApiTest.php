<?php

namespace Tests\Feature;

use App\Enums\CheckInSource;
use App\Models\Member;
use App\Models\Product;
use App\Services\CheckInService;
use App\Services\SubscriptionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesOpsFixtures;
use Tests\TestCase;

class PortalApiTest extends TestCase
{
    use RefreshDatabase, CreatesOpsFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedSettings();
    }

    private function login(Member $member): string
    {
        return $this->postJson('/api/v1/portal/login', [
            'member_code' => $member->member_code,
            'phone' => $member->phone,
        ])->assertOk()->json('token');
    }

    public function test_login_returns_token_with_expiry_envelope(): void
    {
        $member = $this->makeMember();

        $this->postJson('/api/v1/portal/login', [
            'member_code' => $member->member_code,
            'phone' => $member->phone,
        ])
            ->assertOk()
            ->assertJson(['result' => 'Login successful', 'expires' => 3600, 'error' => null])
            ->assertJsonPath('member_code', $member->member_code)
            ->assertJsonStructure(['token']);
    }

    public function test_wrong_credentials_are_rejected(): void
    {
        $member = $this->makeMember();

        $this->postJson('/api/v1/portal/login', [
            'member_code' => $member->member_code,
            'phone' => 'wrong',
        ])->assertUnauthorized()->assertJsonPath('error', 'Invalid member code or phone.');
    }

    public function test_blacklisted_member_cannot_log_in(): void
    {
        $member = $this->makeMember(['status' => 'blacklisted']);

        $this->postJson('/api/v1/portal/login', [
            'member_code' => $member->member_code,
            'phone' => $member->phone,
        ])->assertForbidden();
    }

    public function test_unauthenticated_requests_are_rejected(): void
    {
        $this->getJson('/api/v1/portal/profile')->assertUnauthorized();
    }

    public function test_token_without_portal_ability_is_rejected(): void
    {
        $member = $this->makeMember();
        $token = $member->createToken('other', ['something:else'])->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/portal/profile')->assertForbidden();
    }

    public function test_profile_and_memberships_shapes(): void
    {
        $gym = $this->makeDepartment();
        $plan = $this->makeMonthlyPlan($gym);
        $member = $this->makeMember();
        app(SubscriptionService::class)->subscribe($member, $plan);
        $token = $this->login($member);

        $this->withToken($token)->getJson('/api/v1/portal/profile')
            ->assertOk()
            ->assertJsonPath('result.member_code', $member->member_code)
            ->assertJsonPath('result.status', 'active');

        $this->withToken($token)->getJson('/api/v1/portal/memberships')
            ->assertOk()
            ->assertJsonPath('result.0.plan', 'Gym Monthly')
            ->assertJsonPath('result.0.status', 'active');
    }

    public function test_balance_reflects_outstanding_invoices(): void
    {
        $gym = $this->makeDepartment();
        $plan = $this->makeMonthlyPlan($gym, ['admission_fee' => 0]);
        $member = $this->makeMember();
        app(SubscriptionService::class)->subscribe($member, $plan); // unpaid 3,500 invoice
        $token = $this->login($member);

        $this->withToken($token)->getJson('/api/v1/portal/balance')
            ->assertOk()
            ->assertJsonPath('result.outstanding_total', 350000)
            ->assertJsonCount(1, 'result.invoices');
    }

    public function test_visits_returns_twelve_month_counts(): void
    {
        $gym = $this->makeDepartment();
        $plan = $this->makeMonthlyPlan($gym);
        $member = $this->makeMember();
        app(SubscriptionService::class)->subscribe($member, $plan);
        app(CheckInService::class)->checkIn($member, $gym, CheckInSource::FrontDesk);
        $token = $this->login($member);

        $response = $this->withToken($token)->getJson('/api/v1/portal/visits')->assertOk();

        $this->assertCount(12, $response->json('result'));
        $this->assertSame(1, collect($response->json('result'))->last()['visits']);
    }

    public function test_products_show_member_pricing(): void
    {
        Product::create([
            'sku' => 'KIT-MOMO', 'name' => 'Steam momo', 'category' => 'kitchen',
            'unit' => 'plate', 'price' => 18000, 'member_price' => 15000,
            'track_stock' => false, 'is_taxable' => false,
        ]);
        $member = $this->makeMember();
        $token = $this->login($member);

        $this->withToken($token)->getJson('/api/v1/portal/products')
            ->assertOk()
            ->assertJsonPath('result.0.price', 18000)
            ->assertJsonPath('result.0.your_price', 15000);
    }

    public function test_purchase_lands_on_the_member_account_and_shows_in_balance(): void
    {
        $product = Product::create([
            'sku' => 'KIT-MOMO', 'name' => 'Steam momo', 'category' => 'kitchen',
            'unit' => 'plate', 'price' => 18000, 'member_price' => 15000,
            'track_stock' => false, 'is_taxable' => false,
        ]);
        $member = $this->makeMember();
        $token = $this->login($member);

        $this->withToken($token)->postJson('/api/v1/portal/products', [
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
        ])
            ->assertOk()
            ->assertJsonPath('result.total', 30000); // member pricing applied

        $this->withToken($token)->getJson('/api/v1/portal/balance')
            ->assertOk()
            ->assertJsonPath('result.outstanding_total', 30000);
    }

    public function test_unknown_product_purchase_is_rejected(): void
    {
        $member = $this->makeMember();
        $token = $this->login($member);

        $this->withToken($token)->postJson('/api/v1/portal/products', [
            'items' => [['product_id' => '019f0000-0000-7000-8000-000000000000', 'quantity' => 1]],
        ])->assertUnprocessable();
    }
}
