<?php

namespace Database\Seeders\Admin;

use App\Models\Admin\AppOnboardScreens;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OnboardScreenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $app_onboard_screens = array(
            array('title' => 'Bank Transfer','sub_title' => 'Users can make faster and more reliable transactions through Payzen.','image' => 'seeder/onboard1.webp','status' => '1','last_edit_by' => '1','created_at' => '2023-05-01 16:33:41','updated_at' => '2023-12-14 09:39:51'),
            array('title' => 'Mobile Transfer','sub_title' => 'Users can make faster and more reliable transactions through Payzen.','image' => 'seeder/onboard2.webp','status' => '1','last_edit_by' => '1','created_at' => '2023-05-01 16:34:33','updated_at' => '2023-12-14 09:41:08'),
            array('title' => 'Make Payment','sub_title' => 'Users can make faster and more reliable transactions through Payzen.','image' => 'seeder/onboard3.webp','status' => '1','last_edit_by' => '1','created_at' => '2023-06-11 12:37:09','updated_at' => '2023-12-14 09:41:52')
        );
        AppOnboardScreens::insert($app_onboard_screens);
    }
}
