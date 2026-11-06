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
     * Seed the application's database with default IMS accounts, Master Data, and Stock Mutations.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@fahdims.test'],
            [
                'name' => 'Fahd Administrator',
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );

        User::updateOrCreate(
            ['email' => 'staff@fahdims.test'],
            [
                'name' => 'Operational Staff',
                'role' => User::ROLE_STAFF,
                'is_active' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );

        $this->call([
            MasterDataSeeder::class,
            StockMutationSeeder::class,
        ]);
    }
}
