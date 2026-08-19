<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Services\NumberSequenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesOpsFixtures;
use Tests\TestCase;

class NumberSequenceTest extends TestCase
{
    use RefreshDatabase, CreatesOpsFixtures;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedSettings();
    }

    public function test_sequences_are_monotonic_and_formatted(): void
    {
        $service = app(NumberSequenceService::class);

        $this->assertSame('BSA-00001', $service->memberCode());
        $this->assertSame('BSA-00002', $service->memberCode());
        $this->assertSame('INV-2082-83-0001', $service->invoiceNumber());
        $this->assertSame('INV-2082-83-0002', $service->invoiceNumber());
        $this->assertSame('RCP-2082-83-0001', $service->receiptNumber());
        $this->assertSame('VCH-2082-83-0001', $service->voucherNumber());
    }

    public function test_invoice_sequence_restarts_per_fiscal_year(): void
    {
        $service = app(NumberSequenceService::class);

        $this->assertSame('INV-2082-83-0001', $service->invoiceNumber());

        Setting::set('current_fiscal_year', '2083-84');

        $this->assertSame('INV-2083-84-0001', $service->invoiceNumber());
    }
}
