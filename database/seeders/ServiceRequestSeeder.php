<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\ServiceArea;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceRequestSeeder extends Seeder
{
    /**
     * كل حالة من حالات status مع نسبة تكرارها التقريبية بمجموع 100
     * (بيولّد بيانات كافية لتغطية كل مسارات الاختبار المهمة).
     */
    private const STATUS_WEIGHTS = [
        'pending_local'          => 12,
        'pending_global'         => 6,
        'processing'             => 12,
        'accepted'               => 5,
        'scheduled'              => 8,
        'inspection_accepted'    => 4,
        'inspection_in_progress' => 3,
        'fault_detected'         => 3,
        'in_progress'            => 6,
        'completed'              => 20,
        'rejected'               => 8,
        'cancel_by_customer'     => 6,
        'cancel_by_provider'     => 3,
        'cancel_by_system'       => 4,
    ];

    private const DESCRIPTIONS = [
        'يوجد تسرب مياه من تحت المغسلة بالمطبخ.',
        'مروحة السقف تصدر صوت غريب ولا تدور بشكل طبيعي.',
        'انقطاع الكهرباء عن غرفة النوم الرئيسية فقط.',
        'تسريب بسيط من صنبور الحمام العلوي.',
        'المكيف لا يبرد بشكل جيد منذ يومين.',
        'يوجد عطل غير معروف بالسخان الكهربائي، يحتاج فحص.',
        'انسداد بمصرف المطبخ.',
        'صوت غريب من لوحة الكهرباء الرئيسية بالمنزل.',
    ];


    public function run(): void
    {
        $customerIds = Customer::pluck('id');
        $categoryIds = ServiceCategory::pluck('id');
        $areaIds = ServiceArea::pluck('id');

        if ($customerIds->isEmpty() || $categoryIds->isEmpty() || $areaIds->isEmpty()) {
            $this->command->warn(
                'لازم تشغّل Customers/ServiceCategories/ServiceAreas Seeders الأول قبل هذا السيدر.'
            );

            return;
        }

        $statuses = $this->buildWeightedStatusPool();

        foreach ($statuses as $index => $status) {
            [$createdAt, $startsAt, $expiresAt] = $this->timestampsFor($status, $index);

            ServiceRequest::create([
                'customer_id' => $customerIds->random(),
                'service_category_id' => $categoryIds->random(),
                'service_area_id' => $areaIds->random(),
                'request_type' => rand(1, 100) <= 70 ? 'specific_fault' : 'unspecified_fault',
                'status' => $status,
                'description' => self::DESCRIPTIONS[array_rand(self::DESCRIPTIONS)],
                'starts_at' => $startsAt,
                'latitude_x' => 33.5138000 + rand(-80, 80) / 10000,
                'longitude_y' => 36.2765000 + rand(-80, 80) / 10000,
                'is_urgent' => rand(1, 100) <= 20,
                'duration_in_minutes' => [30, 60, 90, 120][array_rand([30, 60, 90, 120])],
                'expires_at' => $expiresAt,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }

        $this->command->info('تم إنشاء '.count($statuses).' طلب خدمة بحالات متنوعة.');
    }

        /**
         * تُنشئ مصفوفة حالات موزّعة حسب النسب أعلاه (مجموعها الكلي = عدد الطلبات).
         */
        private function buildWeightedStatusPool(): array
    {
        $pool = [];

        foreach (self::STATUS_WEIGHTS as $status => $count) {
            $pool = array_merge($pool, array_fill(0, $count, $status));
        }

        shuffle($pool);

        return $pool;
    }

        /**
         * starts_at وexpires_at دايماً معتمدين على created_at (نافذة البحث
         * المحلي: ساعة واحدة من لحظة الإنشاء)، بغض النظر عن الحالة النهائية،
         * لأنهم بيُحسبوا مرة وحدة بس عند الإنشاء ومايتغيروش بعدها.
         *
         * كل خامس طلب من نوع pending_local/pending_global/processing
         * بيتعمّد إنه يكون "متأخر" (expires_at بالماضي) لتختبر عليه
         * أمر التحويل التلقائي لـ cancel_by_system.
         */
        private function timestampsFor(string $status, int $index): array
    {
        $isSearchingStatus = in_array($status, ['pending_local', 'pending_global', 'processing'], true);
        $shouldBeStale = $isSearchingStatus && $index % 5 === 0;

        $createdAt = $shouldBeStale
            ? now()->subHours(rand(2, 6))
            : now()->subMinutes(rand(1, 40));

        $startsAt = $createdAt->copy();
        $expiresAt = $startsAt->copy()->addHour();

        return [$createdAt, $startsAt, $expiresAt];
    }

}
