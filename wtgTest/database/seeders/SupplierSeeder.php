<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Supplier::firstOrCreate(['name' => 'supplier-a']);
        Supplier::firstOrCreate(['name' => 'supplier-b']);
    }
}
