<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [

            ['محمد', 'الأحمد'],
            ['أحمد', 'اليوسف'],
            ['خالد', 'العلي'],
            ['رامي', 'الخطيب'],
            ['محمود', 'الحسن'],
            ['سعيد', 'مصطفى'],
            ['عمر', 'الحموي'],
            ['حسام', 'العبدالله'],
            ['عبدالله', 'العمر'],
            ['ياسر', 'الديب'],
            ['نور', 'الزعبي'],
            ['ليث', 'الحمادة'],
            ['وسام', 'الموسى'],
            ['أيهم', 'الشامي'],
            ['علي', 'قاسم'],

        ];

        foreach ($customers as $index => $customer) {

            $user = User::create([
                'first_name' => $customer[0],
                'last_name' => $customer[1],
                'phone_number' => '0999000' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'password' => Hash::make('Customer123'),
                'phone_verified_at' => now(),
            ]);

            Customer::create([
                'user_id' => $user->id,
                'service_area_id' => rand(1, 12),
                'status' => 'active',
            ]);
        }
    }
}
