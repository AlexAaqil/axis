<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin_password = Hash::make(env("DEFAULT_ADMIN_PASSWORD"));
        $user_password = Hash::make(env("DEFAULT_USER_PASSWORD"));

        $users = [
            [
                "name" => "Admin Test",
                "email" => "admin@gmail.com",
                "phone_number" => "254746055487",
                "password"=> $admin_password,
                "email_verified_at" => "2025-04-29 04:00:42",
            ],
            [
                "name" => "User Test",
                "email" => "user@gmail.com",
                "phone_number" => "254746055487",
                "password"=> $user_password,
                "email_verified_at" => "2025-04-29 04:00:42",
            ],
        ];

        foreach($users as $user) {
            User::firstOrCreate(
                ['email' => $user['email']],
                $user
            );
        }
    }
}
