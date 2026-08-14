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

    /*
    |--------------------------------------------------------------------------
    | أوصاف الطلبات
    |--------------------------------------------------------------------------
    */

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

    /*
    |--------------------------------------------------------------------------
    | Run Seeder
    |--------------------------------------------------------------------------
    */

    public function run(): void
    {
        $customerIds = Customer::pluck('id');

        $categoryIds = ServiceCategory::pluck('id');

        /*
        |--------------------------------------------------------------------------
        | نحتاج المناطق كاملة وليس فقط IDs
        | لأننا نريد استخدام latitude / longitude الخاصة بالمنطقة
        |--------------------------------------------------------------------------
        */

        $areas = ServiceArea::all();

        /*
        |--------------------------------------------------------------------------
        | التأكد من وجود البيانات الأساسية
        |--------------------------------------------------------------------------
        */

        if (
            $customerIds->isEmpty() ||
            $categoryIds->isEmpty() ||
            $areas->isEmpty()
        ) {
            $this->command->warn(
                'لازم تشغّل Customers/ServiceCategories/ServiceAreas Seeders الأول قبل هذا السيدر.'
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | إنشاء حالات الطلبات حسب النسب
        |--------------------------------------------------------------------------
        */

        $statuses = $this->buildWeightedStatusPool();

        foreach ($statuses as $index => $status) {

            /*
            |--------------------------------------------------------------------------
            | اختيار منطقة للطلب
            |--------------------------------------------------------------------------
            */

            $area = $areas->random();

            /*
            |--------------------------------------------------------------------------
            | إنشاء التواريخ
            |--------------------------------------------------------------------------
            */

            [
                $createdAt,
                $startsAt,
                $expiresAt
            ] = $this->timestampsFor($status, $index);
            /*
            |--------------------------------------------------------------------------
            | إنشاء موقع الطلب
            |
            | مهم:
            | إحداثيات الطلب ليست نفسها إحداثيات المنطقة.
            |
            | نستخدم مركز المنطقة كنقطة قريبة فقط لإنشاء
            | بيانات تجريبية منطقية.
            |--------------------------------------------------------------------------
            */

            $requestLatitude = $area->latitude_x
                + fake()->randomFloat(6, -0.01, 0.01);

            $requestLongitude = $area->longitude_y
                + fake()->randomFloat(6, -0.01, 0.01);

            /*
            |--------------------------------------------------------------------------
            | إنشاء الطلب
            |--------------------------------------------------------------------------
            */

            ServiceRequest::create([

                'customer_id' =>
                    $customerIds->random(),

                'service_category_id' =>
                    $categoryIds->random(),

                /*
                | المنطقة التي ينتمي إليها الطلب
                */

                'service_area_id' =>
                    $area->id,

                'request_type' =>
                    rand(1, 100) <= 70
                        ? 'specific_fault'
                        : 'unspecified_fault',

                'status' =>
                    $status,

                'description' =>
                    self::DESCRIPTIONS[
                        array_rand(self::DESCRIPTIONS)
                    ],

                'starts_at' =>
                    $startsAt,

                /*
                |--------------------------------------------------------------------------
                | موقع الطلب نفسه
                |--------------------------------------------------------------------------
                */

                'latitude_x' =>
                    $requestLatitude,

                'longitude_y' =>
                    $requestLongitude,

                'is_urgent' =>
                    rand(1, 100) <= 20,

                'duration_in_minutes' =>
                    [30, 60, 90, 120][
                        array_rand([30, 60, 90, 120])
                    ],

                'expires_at' =>
                    $expiresAt,

                'created_at' =>
                    $createdAt,

                'updated_at' =>
                    $createdAt,
            ]);
        }

        $this->command->info(
            'تم إنشاء ' .
            count($statuses) .
            ' طلب خدمة بحالات متنوعة.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | إنشاء Pool للحالات
    |--------------------------------------------------------------------------
    */

    private function buildWeightedStatusPool(): array
    {
        $pool = [];

        foreach (self::STATUS_WEIGHTS as $status => $count) {

            $pool = array_merge(
                $pool,
                array_fill(0, $count, $status)
            );
        }

        shuffle($pool);

        return $pool;
    }

    /*
    |--------------------------------------------------------------------------
    | إنشاء التواريخ
    |--------------------------------------------------------------------------
    |
    | الطلبات التي تكون pending_local أو pending_global أو processing
    | بعضها نجعله منتهيًا حتى نستطيع اختبار cancel_by_system.
    |
    |--------------------------------------------------------------------------
    */

    private function timestampsFor(
        string $status,
        int $index
    ): array {

        $isSearchingStatus = in_array(
            $status,
            [
                'pending_local',
                'pending_global',
                'processing'
            ],
            true
        );

        $shouldBeStale =
            $isSearchingStatus &&
            $index % 5 === 0;

            $createdAt = $shouldBeStale
            ? now()->subHours(rand(2, 6))
            : now()->subMinutes(rand(1, 40));

        $startsAt = $createdAt->copy();

        $expiresAt = $startsAt
            ->copy()
            ->addHour();

        return [
            $createdAt,
            $startsAt,
            $expiresAt
        ];
    }
}