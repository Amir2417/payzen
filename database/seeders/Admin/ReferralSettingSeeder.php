<?php

namespace Database\Seeders\Admin;

use App\Models\Admin\ReferralSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReferralSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $referral_settings = array(
            array('id' => '1','bonus' => '10.00000000','wallet_type' => 'p_balance','mail' => '0','status' => '1','created_at' => '2023-10-15 17:37:57','updated_at' => '2023-10-15 17:38:14')
        );
        ReferralSetting::insert($referral_settings);
    }
}
