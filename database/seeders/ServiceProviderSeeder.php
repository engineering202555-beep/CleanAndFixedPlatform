<?php

namespace Database\Seeders;

use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ServiceProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $providers = [

//السباكة والصحية
            ['أحمد','السيد',0],
            ['محمد','الشامي',0],
            ['رامي','الحلاق',0],
            ['عمار','الديب',0],

            //الكهرباء المنزلي
            ['خالد','العلي',1],
            ['عبدالله','الحموي',1],
            ['وسيم','الزعبي',1],
            ['يزن','الحسن',1],

          //صيانة المولدات والطاقة الشمسية
            ['علي','الخطيب',2],
            ['نور','الديب',2],

            //التكييف والتبريد
            ['ليث','الموسى',3],
            ['سامر','العمر',3],

            //النجارة والغالونات
            ['محمود','المصطفى',4],
            ['حسام','عبدو',4],
            ['أغيد','الموسى',4],

//النظافة الشاملة
            ['أيهم','القاسم',5],
            ['فراس','الحسن',5],
            ['سعيد','العلي',5],
            ['هيفاء','العلي',5],
            ['راميا','عيسى',5],


        ];

        foreach ($providers as $index => $provider) {

            $user = User::create([

                'first_name' => $provider[0],

                'last_name' => $provider[1],

                'phone_number' => '0988000'.str_pad($index + 1,3,'0',STR_PAD_LEFT),

                'password' => Hash::make('Provider123'),

                'phone_verified_at' => now(),

            ]);

            ServiceProvider::create([

                'user_id' => $user->id,

                'service_category_id' => $provider[2],

                'service_area_id' => rand(1,5),

                'inspection_price' => rand(20,50),

                'bio' => 'مقدم خدمة بخبرة عالية في مجال العمل.',

                'experience_years' => rand(2,15),

                'is_approved' => true,

                'rating' => rand(35,50)/10,

                'working_from' => '08:00:00',

                'working_to' => '18:00:00',

                'latitude' => 33.5138000 + rand(-50,50)/10000,

                'longitude' => 36.2765000 + rand(-50,50)/10000,

                'account_status' => 'active',

                'availability_status' => 'available',

            ]);

        }

    }
}
