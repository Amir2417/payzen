<?php

namespace Database\Seeders\Admin;


use Illuminate\Database\Seeder;

class SetupEmailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $env_modify_keys = [
            "MAIL_MAILER"       => "smtp",
            "MAIL_HOST"         => "smtp.titan.email",
            "MAIL_PORT"         => "587",
            "MAIL_USERNAME"     => "do-not-reply@appdevs.net",
            "MAIL_PASSWORD"     => "QP2fsLk?80Ac",
            "MAIL_ENCRYPTION"   => "tls",
            "MAIL_FROM_ADDRESS" => "noreply@appdevs.net",
            "MAIL_FROM_NAME"    => "Payzen",
        ];

        modifyEnv($env_modify_keys);
    }
}
