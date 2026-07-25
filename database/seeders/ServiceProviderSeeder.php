<?php

namespace Database\Seeders;

use App\Models\ServiceProvider;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ServiceProviderSeeder extends Seeder
{

    private const REJECTION_REASONS = [
        'الوثائق المرفقة غير واضحة، يرجى إعادة الرفع.',
        'رخصة العمل منتهية الصلاحية.',
        'معلومات الخبرة المذكورة غير متطابقة مع الوثائق المرفقة.',
        'صورة الهوية غير مطابقة للاسم المسجّل.',
    ];

    public function run(): void
    {
        $providers = [
            // السباكة والصحية
            ['أحمد', 'السيد', 1],
            ['محمد', 'الشامي', 1],
            ['رامي', 'الحلاق', 1],
            ['عمار', 'الديب', 1],

            // الكهرباء المنزلي
            ['خالد', 'العلي', 2],
            ['عبدالله', 'الحموي', 2],
            ['وسيم', 'الزعبي', 2],
            ['يزن', 'الحسن', 2],

            // صيانة المولدات والطاقة الشمسية
            ['علي', 'الخطيب', 3],
            ['نور', 'الديب', 3],

            // التكييف والتبريد
            ['ليث', 'الموسى', 4],
            ['سامر', 'العمر', 4],

            // النجارة والغالونات
            ['محمود', 'المصطفى', 5],
            ['حسام', 'عبدو', 5],
            ['أغيد', 'الموسى', 5],

            // النظافة الشاملة
            ['أيهم', 'القاسم', 6],
            ['فراس', 'الحسن', 6],
            ['سعيد', 'العلي', 6],
            ['هيفاء', 'العلي', 6],
            ['راميا', 'عيسى', 6],
        ];

        foreach ($providers as $index => $provider) {
            $user = User::create([
                'first_name' => $provider[0],
                'last_name' => $provider[1],
                'phone_number' => '0988000'.str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                'password' => Hash::make('Provider123'),
                'phone_verified_at' => now(),
            ]);

            [$accountStatus, $availabilityStatus, $rejectionReason] = $this->randomStatus();

            ServiceProvider::create([
                'user_id' => $user->id,
                'service_category_id' => $provider[2],
                'service_area_id' => rand(1, 12),
                'inspection_price' => rand(20, 50),
                'bio' => 'مقدم خدمة بخبرة عالية في مجال العمل.',
                'experience_years' => rand(2, 15),
                'rating' => rand(35, 50) / 10,
                'working_from' => '08:00:00',
                'working_to' => '18:00:00',
                'latitude' => 33.5138000 + rand(-50, 50) / 10000,
                'longitude' => 36.2765000 + rand(-50, 50) / 10000,
                'account_status' => $accountStatus,
                'availability_status' => $availabilityStatus,
                'rejection_reason' => $rejectionReason,
            ]);
        }
    }


    private function randomStatus(): array
    {
        $roll = rand(1, 100);

        return match (true) {
            $roll <= 50 => ['active', rand(0, 1) ? 'available' : 'offline', null],
            $roll <= 80 => ['pending', 'offline', null],
            default => ['rejected', 'offline', self::REJECTION_REASONS[array_rand(self::REJECTION_REASONS)]],
        };
    }
}
