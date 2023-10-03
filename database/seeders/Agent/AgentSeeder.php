<?php

namespace Database\Seeders\Agent;

use App\Models\Agent;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AgentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'firstname'         => "Test",
                'lastname'          => "Agent",
                'email'             => "agent@appdevs.net",
                'username'          => "testagent",
                'mobile_code'       => "880",
                'mobile'            => "1791205437",
                'full_mobile'       => "8801791205437",
                'status'            => true,
                'password'          => Hash::make("appdevs"),
                'address'           => '{"country":"United States","city":"Dhaka","zip":"1230","state":"","address":""}',
                'email_verified'    => true,
                'sms_verified'      => true,
                'kyc_verified'      => true,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'firstname'         => "Test",
                'lastname'          => "Agent2",
                'email'             => "agent2@appdevs.net",
                'username'          => "appdevs",
                'mobile_code'       => "880",
                'mobile'            => "12345678",
                'full_mobile'       => "88012345678",
                'status'            => true,
                'password'          => Hash::make("appdevs"),
                'address'           => '{"country":"United States","city":"Dhaka","zip":"1230","state":"","address":""}',
                'email_verified'    => true,
                'sms_verified'      => true,
                'kyc_verified'      => true,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],

        ];

        Agent::insert($data);
    }
}
