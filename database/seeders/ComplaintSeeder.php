<?php

namespace Database\Seeders;

use App\Models\Complaint;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ComplaintSeeder extends Seeder
{
    private const TOTAL = 25;

    private const REASONS = [
        'provider_behavior',
        'poor_service',
        'late_arrival',
        'payment_issue',
        'technical_problem',
        'other',
    ];

    private const STATUSES = ['pending', 'in_review', 'resolved', 'rejected'];

    private const DESCRIPTIONS = [
        'مقدم الخدمة تأخر عن الموعد المحدد أكثر من ساعة بدون إخبار مسبق.',
        'جودة التصليح لم تكن بالمستوى المتوقع، العطل عاد بعد يومين.',
        'الزبون لم يكن متواجداً بالموعد المحدد رغم التأكيد المسبق.',
        'تم الاتفاق على سعر ثم طلب مقدم الخدمة مبلغاً إضافياً عند الوصول.',
        'تعامل غير لائق أثناء تنفيذ الخدمة.',
        'مشكلة تقنية بالتطبيق أثناء تقديم العرض.',
    ];

    public function run(): void
    {
        $userIds = User::pluck('id');
        $requestIds = ServiceRequest::pluck('id');

        if ($userIds->count() < 2) {
            $this->command->warn('لازم يكون في مستخدمين كفاية (زبائن ومقدمي خدمة) قبل تشغيل هذا السيدر.');

            return;
        }

        for ($i = 0; $i < self::TOTAL; $i++) {
            $userId = $userIds->random();

            // 70% من الشكاوى موجهة ضد مستخدم معيّن، والباقي شكوى عامة
            // (against_user_id = null، مثلاً شكوى على التطبيق نفسه).
            $againstUserId = rand(1, 100) <= 70
                ? $userIds->reject(fn ($id) => $id === $userId)->random()
                : null;

            $status = self::STATUSES[array_rand(self::STATUSES)];
            $isDecided = in_array($status, ['resolved', 'rejected'], true);

            Complaint::create([
                'user_id' => $userId,
                'against_user_id' => $againstUserId,
                // شكوى عامة (بدون against_user_id) غالباً مش مرتبطة
                // بطلب معيّن، فمنسيبها بدون service_request_id غالباً.
                'service_request_id' => $againstUserId && rand(1, 100) <= 60
                    ? $requestIds->random()
                    : null,
                'reason' => self::REASONS[array_rand(self::REASONS)],
                'description' => self::DESCRIPTIONS[array_rand(self::DESCRIPTIONS)],
                'status' => $status,
                'admin_notes' => $isDecided ? $this->adminNoteFor($status) : null,
            ]);
        }

        $this->command->info('تم إنشاء '.self::TOTAL.' شكوى بحالات وأسباب متنوعة.');
    }

    private function adminNoteFor(string $status): string
    {
        return $status === 'resolved'
            ? 'تم التواصل مع الطرفين وحل المشكلة وديّاً.'
            : 'تم رفض الشكوى لعدم كفاية الأدلة المقدمة.';
    }
}
