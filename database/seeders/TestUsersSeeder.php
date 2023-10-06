<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // To run only this seeder, use the command:
        // php artisan db:seed --class=TestUsersSeeder

        // Loop creating 50 random users.
        // Mass renaming in Windows automatically names files in this format.
        // By default, first user will have login credentials test@test test, and should be used for testing.
        $statusOptions = ['active', 'inactive', 'banned'];

        DB::table('users')->insert([
            'nickname' => 'The Tester',
            'password' => Hash::make('test'),
            'email' => 'test@test',
            'country_code' => 'POL',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        for ($i = 0; $i < 50; $i++) {
            DB::table('users')->insert([
                'nickname' => Str::random(10),
                'password' => Hash::make('password'),
                'email' => Str::random(10) . '@example.com',
                'country_code' => Str::upper(Str::random(3)),
                'status' => $statusOptions[array_rand($statusOptions)],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}