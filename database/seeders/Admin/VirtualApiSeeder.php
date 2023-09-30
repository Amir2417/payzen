<?php

namespace Database\Seeders\Admin;

use App\Models\VirtualCardApi;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VirtualApiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $virtual_card_apis = array(
            array('admin_id' => '1','image' => 'seeder/virtual-card.png','card_details' => 'This card is property of QRPay, Wonderland. Misuse is criminal offence. If found, please return to QRPay or to the nearest bank.','config' => '{"flutterwave_secret_key":"FLWSECK_TEST-SANDBOXDEMOKEY-X","flutterwave_secret_hash":"AYxcfvgbhnj@34","flutterwave_url":"https:\/\/api.flutterwave.com\/v3","sudo_api_key":"eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJfaWQiOiI2NGI2NWExZmZjM2I2NDM5ZjdkNTZjYzIiLCJlbWFpbEFkZHJlc3MiOiJ1c2VyQGFwcGRldnMubmV0IiwianRpIjoiNjRiNjYyNjdmYzNiNjQzOWY3ZDViZjI2IiwibWVtYmVyc2hpcCI6eyJfaWQiOiI2NGI2NWExZmZjM2I2NDM5ZjdkNTZjYzUiLCJidXNpbmVzcyI6eyJfaWQiOiI2NGI2NWExZmZjM2I2NDM5ZjdkNTZjYzAiLCJuYW1lIjoiQXBwZGV2c1giLCJpc0FwcHJvdmVkIjpmYWxzZX0sInVzZXIiOiI2NGI2NWExZmZjM2I2NDM5ZjdkNTZjYzIiLCJyb2xlIjoiQVBJS2V5In0sImlhdCI6MTY4OTY3NDM0MywiZXhwIjoxNzIxMjMxOTQzfQ.MTKO352CEfxG4SUhpfAWu3mkHilLL8Y-oufD6WWCiH4","sudo_vault_id":"tntbuyt0v9u","sudo_url":"https:\/\/api.sandbox.sudo.cards","sudo_mode":"sandbox","name":"sudo"}','created_at' => now(),'updated_at' => now())
          );

        VirtualCardApi::insert($virtual_card_apis);
    }
}
