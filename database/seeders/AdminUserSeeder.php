<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'kwizera.ibrahim@gmail.com'],
            [
                'name' => 'Ibrahim Kwizera',
                'password' => Hash::make('admin'),
            ]
        );

        $user->syncRoles(['super_admin', 'owner']);
    }
}
