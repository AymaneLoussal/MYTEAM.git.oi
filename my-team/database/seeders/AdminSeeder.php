<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('Demo admin seeding is restricted to local/testing environments.');
        }
        User::firstOrCreate(['email' => 'admin@team.com'], [
            'name' => 'Admin',
            'email' => 'admin@team.com',
            'password' => Hash::make('adminpass'),
            'is_admin' => true,
        ]);
    }
}
