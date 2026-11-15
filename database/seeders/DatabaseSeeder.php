<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@barokahmart.test'],
            [
                'name' => 'Admin Toko',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        User::firstOrCreate(
            ['email' => 'kasir@barokahmart.test'],
            [
                'name' => 'Kasir Rina',
                'password' => Hash::make('password'),
                'role' => 'kasir',
            ]
        );

        $this->call(CategorySeeder::class);
    }
}
