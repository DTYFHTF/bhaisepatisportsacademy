<?php

namespace App\Http\Controllers\Api;

use App\Enums\MemberStatus;
use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Product;
use App\Services\PosService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Member Portal API — the backend for a future member app/portal.
 *
 * Endpoint set and response envelope modeled on the GymMaster Member
 * Portal API (https://www.gymmaster.com/gymmaster-api/): token login,
 * profile, memberships, outstanding balance, account history, visit
 * counts, and product purchase on account, all wrapped as
 * {result, error}.
 */
class PortalController extends Controller
{
    private const TOKEN_TTL_SECONDS = 3600;

    /**
     * v1 credential: member code + registered phone. (SMS OTP is the
     * planned upgrade; the public website already owns an OTP service.)
     */
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'member_code' => ['required', 'string'],
            'phone' => ['required', 'string'],
        ]);

        $member = Member::query()
            ->where('member_code', $data['member_code'])
            ->where('phone', $data['phone'])
            ->first();

        if (! $member) {
            return $this->error('Invalid member code or phone.', 401);
        }

        if ($member->status === MemberStatus::Blacklisted) {
            return $this->error('This account is disabled. Please contact the front desk.', 403);
        }

        $token = $member->createToken(
            'portal',
            ['portal:member'],
            now()->addSeconds(self::TOKEN_TTL_SECONDS),
        );

        return response()->json([
            'result' => 'Login successful',
            'token' => $token->plainTextToken,
            'expires' => self::TOKEN_TTL_SECONDS,
            'member_code' => $member->member_code,
            'error' => null,
        ]);
    }

    public function profile(Request $request): JsonResponse
    {
        $member = $this->member($request);

        return $this->result([
            'member_code' => $member->member_code,
            'name' => $member->full_name,
            'photo_url' => $member->photo_url,
            'phone' => $member->phone,
            'email' => $member->email,
            'status' => $member->status->value,
            'joined_on' => $member->joined_on->toDateString(),
        ]);
    }

    public function memberships(Request $request): JsonResponse
    {
        $member = $this->member($request);

        $memberships = $member->subscriptions()
            ->with('plan')
            ->orderByDesc('starts_on')
            ->get()
            ->map(fn ($sub) => [
                'plan' => $sub->plan->name,
                'plan_code' => $sub->plan->code,
                'status' => $sub->status->value,
                'starts_on' => $sub->starts_on->toDateString(),
                'ends_on' => $sub->ends_on?->toDateString(),
                'sessions_remaining' => $sub->sessions_remaining,
                'sessions_total' => $sub->sessions_total,
            ]);

        return $this->result($memberships);
    }

    public function balance(Request $request): JsonResponse
    {
        $member = $this->member($request);

        $open = $member->invoices()
            ->outstanding()
            ->orderBy('due_date')
            ->get()
            ->map(fn ($invoice) => [
                'invoice_number' => $invoice->invoice_number,
                'issue_date' => $invoice->issue_date->toDateString(),
                'due_date' => $invoice->due_date->toDateString(),
                'total' => $invoice->total,
                'balance' => $invoice->balance,
                'status' => $invoice->status->value,
                'source' => $invoice->source->value,
            ]);

        return $this->result([
            'outstanding_total' => $member->outstandingBalance(),
            'currency' => 'NPR (paisa)',
            'invoices' => $open,
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        $member = $this->member($request);

        $invoices = $member->invoices()
            ->whereNull('voided_at')
            ->orderByDesc('issue_date')
            ->limit(50)
            ->get()
            ->map(fn ($invoice) => [
                'kind' => 'charge',
                'reference' => $invoice->invoice_number,
                'date' => $invoice->issue_date->toDateString(),
                'amount' => $invoice->total,
                'status' => $invoice->status->value,
            ]);

        $payments = $member->payments()
            ->completed()
            ->orderByDesc('received_at')
            ->limit(50)
            ->get()
            ->map(fn ($payment) => [
                'kind' => 'payment',
                'reference' => $payment->receipt_number,
                'date' => $payment->received_at->toDateString(),
                'amount' => $payment->amount,
                'method' => $payment->method->value,
            ]);

        return $this->result(
            $invoices->concat($payments)->sortByDesc('date')->values()
        );
    }

    public function visits(Request $request): JsonResponse
    {
        $member = $this->member($request);

        // Grouped in PHP for sqlite/mysql parity, like the report pages.
        $months = collect(range(11, 0))
            ->map(fn (int $i) => now()->subMonthsNoOverflow($i)->startOfMonth());

        $visits = $months->map(fn ($month) => [
            'month' => $month->format('Y-m'),
            'visits' => $member->checkIns()
                ->allowed()
                ->whereBetween('checked_in_at', [$month, $month->copy()->endOfMonth()])
                ->count(),
        ]);

        return $this->result($visits);
    }

    public function products(Request $request): JsonResponse
    {
        $member = $this->member($request);

        $products = Product::query()
            ->active()
            ->whereIn('category', ['shop', 'kitchen'])
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->map(fn (Product $product) => [
                'id' => $product->id,
                'sku' => $product->sku,
                'name' => $product->name,
                'category' => $product->category->value,
                'unit' => $product->unit,
                'price' => $product->price,
                'your_price' => $product->priceFor($member),
                'in_stock' => $product->track_stock ? $product->stock_on_hand > 0 : true,
            ]);

        return $this->result($products);
    }

    /**
     * Purchase on account — GymMaster's POST /v2/products semantics:
     * the charge lands on the member's account, payable at the desk.
     */
    public function purchase(Request $request, PosService $pos): JsonResponse
    {
        $member = $this->member($request);

        $data = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:20'],
            'items.*.product_id' => ['required', 'uuid'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $lines = collect($data['items'])
            ->map(function (array $item) {
                $product = Product::query()->active()
                    ->whereIn('category', ['shop', 'kitchen'])
                    ->find($item['product_id']);

                if (! $product) {
                    throw ValidationException::withMessages(['items' => 'Unknown product.']);
                }

                return ['product' => $product, 'quantity' => (int) $item['quantity']];
            })
            ->all();

        try {
            $invoice = $pos->sale($lines, $member, PosService::ON_ACCOUNT);
        } catch (ValidationException $e) {
            return $this->error(collect($e->errors())->flatten()->first(), 422);
        }

        return $this->result([
            'message' => 'Purchase placed on your account.',
            'invoice_number' => $invoice->invoice_number,
            'total' => $invoice->total,
            'balance' => $invoice->balance,
        ]);
    }

    // ---- Helpers ----

    private function member(Request $request): Member
    {
        $member = $request->user();

        abort_unless($member instanceof Member, 403, 'This endpoint is for members only.');
        abort_unless($member->tokenCan('portal:member'), 403, 'Token lacks the portal:member ability.');

        return $member;
    }

    private function result(mixed $result): JsonResponse
    {
        return response()->json(['result' => $result, 'error' => null]);
    }

    private function error(string $message, int $status): JsonResponse
    {
        return response()->json(['result' => null, 'error' => $message], $status);
    }
}
