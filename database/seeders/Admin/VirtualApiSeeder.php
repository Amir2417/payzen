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
            array('admin_id' => '1','image' => 'seeder/virtual-card.png','card_details' => 'This card is property of QRPay, Wonderland. Misuse is criminal offence. If found, please return to QRPay or to the nearest bank.','config' => '{"flutterwave_secret_key":"FLWSECK_TEST-SANDBOXDEMOKEY-X","flutterwave_secret_hash":"AYxcfvgbhnj@34","flutterwave_url":"https:\/\/api.flutterwave.com\/v3","sudo_api_key":"eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJfaWQiOiI2NTY3MDBiYmQxNTQwNzYyMzA1ZWUyNjMiLCJlbWFpbEFkZHJlc3MiOiJ1c2VyM0BhcHBkZXZzLm5ldCIsImp0aSI6IjY1NjcwMTc3ZDE1NDA3NjIzMDVlZWIxNyIsIm1lbWJlcnNoaXAiOnsiX2lkIjoiNjU2NzAwYmJkMTU0MDc2MjMwNWVlMjY2IiwiYnVzaW5lc3MiOnsiX2lkIjoiNjU2NzAwYmJkMTU0MDc2MjMwNWVlMjYxIiwibmFtZSI6IkFwcERldnMiLCJpc0FwcHJvdmVkIjpmYWxzZX0sInVzZXIiOiI2NTY3MDBiYmQxNTQwNzYyMzA1ZWUyNjMiLCJyb2xlIjoiQVBJS2V5In0sImlhdCI6MTcwMTI0OTM5OSwiZXhwIjoxNzMyODA2OTk5fQ.oB0i1Hn_MMLM3tZpbAEqU6YlDIqtk_yJT25EGhE021E","sudo_vault_id":"tntbuyt0v9u","sudo_url":"https://api.sandbox.sudo.cards","sudo_mode":"sandbox","name":"sudo"}','created_at' => now(),'updated_at' => now())
          );

        VirtualCardApi::insert($virtual_card_apis);
    }
}
