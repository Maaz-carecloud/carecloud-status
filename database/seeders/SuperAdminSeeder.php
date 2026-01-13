<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Muhammad Maaz',
            'email' => 'muhammadmaaz2@carecloud.com',
            'password' => Hash::make('Admin1234#'),
            'role' => UserRole::SUPER_ADMIN,
            'email_verified_at' => now(),
        ]);

        $this->command->info('Super Admin created successfully!');
    }
}
