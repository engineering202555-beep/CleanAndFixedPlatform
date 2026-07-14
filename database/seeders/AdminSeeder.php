<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Admin::updateOrCreate(
            ['user_name' => 'serva_admin'],
            [
                'password' => Hash::make('12345678serva'),
                'must_change_password' => true,
            ]
        );
        $admin->assignRole('admin');
    }
}
