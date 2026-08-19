<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        $suppliers = [
            ['name' => 'Himalayan Sports Distributors', 'contact_person' => 'Rajiv Shrestha', 'phone' => '9851022001', 'pan_number' => '301234567', 'address' => 'Tripureshwor, Kathmandu', 'notes' => 'Yonex / Li-Ning authorised distributor'],
            ['name' => 'Lalitpur Fresh Suppliers', 'contact_person' => 'Kanchha Maharjan', 'phone' => '9841033002', 'address' => 'Mangal Bazaar, Lalitpur', 'notes' => 'Kitchen produce, daily delivery'],
            ['name' => 'Everest Beverage Wholesale', 'contact_person' => 'Sarita Karki', 'phone' => '9861044003', 'pan_number' => '302345678', 'address' => 'Balkumari, Lalitpur'],
            ['name' => 'Patan Hardware & General', 'contact_person' => 'Hari Prajapati', 'phone' => '9808055004', 'address' => 'Lagankhel, Lalitpur', 'notes' => 'Pool chemicals, cleaning, hardware'],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::updateOrCreate(['name' => $supplier['name']], [...$supplier, 'is_active' => true]);
        }
    }
}
