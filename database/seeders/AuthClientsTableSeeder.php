<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Laravel\Passport\Passport;

class AuthClientsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Passport::client()->create([
            'user_id' => null,
            'name' => 'Mobile App',
            'secret' => env('PASSPORT_CLIENT_SECRET', '1dvxrJwoSD287sqsxJENNSh2hglXivbyqRbTPNU2'),
            'redirect' => config('app.url'),
            'personal_access_client' => 0,
            'password_client' => 1,
            'revoked' => false,
        ]);
    }
}
