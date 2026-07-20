<?php

namespace Database\Seeders;

use App\Models\Subscription;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       Subscription::insert([

               [
                   'type' => 'free',
                   'requests_per_month' => 3,
                   'price' => 0.00,
                   'duration_in_days' => 30,
                   'description' => 'الباقة التجريبية المجانية، تتيح للمزود استقبال حتى 3 طلبات صيانة شهرياً لاختبار جودة التطبيق.',
                   'is_active' => true
               ],
               [
                   'type' => 'paid',
                   'requests_per_month' => 100,
                   'price' => 75000.00, // 75,000 ليرة سورية شهرياً
                   'duration_in_days' => 30,
                   'description' => 'باقة الحرفي النشط الفضية، تتيح استقبال لغاية 100 طلباً شهرياً لزيادة فرصة الحصول على زبائن.',
                   'is_active' => true
               ],
               [
                   'type' => 'paid',
                   'requests_per_month' => 120,
                   'price' => 180000.00, // 180,000 ليرة سورية شهرياً للورش الكبيرة
                   'duration_in_days' => 30,
                   'description' => 'الباقة الذهبية الممتازة للورش والشركات، تتيح استقبال حتى 120 طلباً شهرياً مع أولوية في الدعم الفني.',
                   'is_active' => true
               ],
       ]);
    }
}
