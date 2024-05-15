<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Vendor;

class VendorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $vendors = [
            ['id' => 6, 'vendor_id' => 'aws_std', 'enabled' => 0, 'cost' => 0.000004],
            ['id' => 7, 'vendor_id' => 'aws_nrl', 'enabled' => 0, 'cost' => 0.000016],
        ];

        foreach ($vendors as $vendor) {
            Vendor::updateOrCreate(['id' => $vendor['id']], $vendor);
        }
    }
}
