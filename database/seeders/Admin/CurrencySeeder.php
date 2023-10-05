<?php

namespace Database\Seeders\Admin;

use App\Models\Admin\Currency;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = array(
            array('admin_id' => '1','country' => 'United States','name' => 'United States dollar','code' => 'USD','symbol' => '$','type' => 'FIAT','flag' => "seeder/currency-flug.webp",'rate' => '1.00000000','sender' => '1','receiver' => '1','default' => '1','status' => '1','created_at' => '2023-07-12 11:23:37','updated_at' => '2023-07-12 11:23:37'),
            array('admin_id' => '1','country' => 'Cote D\'Ivoire (Ivory Coast)','name' => 'West African CFA franc','code' => 'XOF','symbol' => 'CFA','type' => 'FIAT','flag' => NULL,'rate' => '626.4100000000','sender' => '1','receiver' => '1','default' => '0','status' => '1','created_at' => now(),'updated_at' => now()),
            array('admin_id' => '1','country' => 'Afghanistan','name' => 'Afghan afghani','code' => 'AFN','symbol' => '؋','type' => 'CRYPTO','flag' => NULL,'rate' => '77.22000000','sender' => '1','receiver' => '1','default' => '0','status' => '1','created_at' => now(),'updated_at' => now())
        );

        Currency::insert($data);
    }
}
