<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        //=========================Basic Users=============================
        \App\Models\User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@arkan.com',
            'password' => Hash::make('admin@123'),
            'role' => 0,
            'phone' => "123456789",
            "country_code" => '+20'
        ]);
        \App\Models\User::factory()->create([
            'name' => 'Amir',
            'email' => 'amir@arkan.com',
            'password' => Hash::make('12345678'),
            'role' => 1,
            'phone' => "1234567890",
            "country_code" => '+20',
            'created_by' => 1
        ]);
        \App\Models\User::factory()->create([
            'name' => 'Samir',
            'email' => 'samir@arkan.com',
            'password' => Hash::make('12345678'),
            'role' => 2,
            'phone' => "1234567891",
            "country_code" => '+20',
            'created_by' => 1
        ]);
        \App\Models\User::factory()->create([
            'name' => 'test',
            'email' => 'test@arkan.com',
            'password' => Hash::make('12345678'),
            'role' => 2,
            'phone' => "1234567892",
            "country_code" => '+20',
            'created_by' => 1
        ]);

        //=========================Basic Languages=============================
        \App\Models\Language::factory()->create([
            'name' => 'العربية',
            'code' => 'ar',
            'created_by' => 1
        ]);
        \App\Models\Language::factory()->create([
            'name' => 'English',
            'code' => 'en',
            'created_by' => 1
        ]);
        \App\Models\Language::factory()->create([
            'name' => 'French',
            'code' => 'fr',
            'created_by' => 1
        ]);
        \App\Models\Language::factory()->create([
            'name' => 'test',
            'code' => 'ts',
            'created_by' => 1
        ]);
    }
}
