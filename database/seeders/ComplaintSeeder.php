<?php

namespace Database\Seeders;

use App\Models\Complaint;
use App\Models\Customer;
use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class ComplaintSeeder extends Seeder
{
    private const TOTAL = 25;

    // نسبة الشكاوى المرتبطة بطلب فعلي (طرفيها مُستنتجين من الطلب
    // نفسه، مش عشوائيين) — الباقي شكاوى عامة غير مرتبطة بطلب.
    private const LINKED_TO_REQUEST_PERCENTAGE = 60;

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
        $customerUserIds = Customer::pluck('user_id', 'id'); // customer_id => user_id
        $providerUserIds = ServiceProvider::pluck('user_id', 'id'); // provider_id => user_id

        // الطلبات يلي فعلاً عندها عرض مقبول = عندها طرفين حقيقيين
        // (زبون + مقدم خدمة) نقدر نبني شكوى منطقية عليهم.
        $requestsWithProvider = ServiceRequest::query()
            ->whereHas('acceptedOffer')
            ->with('acceptedOffer:id,service_request_id,service_provider_id')
            ->get(['id', 'customer_id']);

        if ($customerUserIds->count() < 1 || $providerUserIds->count() < 1) {
            $this->command->warn('لازم يكون في زبائن ومقدمي خدمة (Customers وServiceProviders) قبل تشغيل هذا السيدر.');

            return;
        }

        $created = 0;

        for ($i = 0; $i < self::TOTAL; $i++) {
            $linkToRequest = rand(1, 100) <= self::LINKED_TO_REQUEST_PERCENTAGE && $requestsWithProvider->isNotEmpty();

            $data = $linkToRequest
                ? $this->buildFromRequest($requestsWithProvider, $customerUserIds, $providerUserIds)
                : $this->buildGeneral($customerUserIds, $providerUserIds);

            if ($data === null) {
                continue;
            }

            $status = self::STATUSES[array_rand(self::STATUSES)];
            $isDecided = in_array($status, ['resolved', 'rejected'], true);

            Complaint::create([
                ...$data,
                'reason'      => self::REASONS[array_rand(self::REASONS)],
                'description' => self::DESCRIPTIONS[array_rand(self::DESCRIPTIONS)],
                'status'      => $status,
                'admin_notes' => $isDecided ? $this->adminNoteFor($status) : null,
            ]);

            $created++;
        }

        $this->command->info("تم إنشاء {$created} شكوى، كل واحدة بين زبون ومقدم خدمة فعلياً (بلا تكرار نفس الدور).");
    }

    /**
     * الطرفين هون مُستنتجين مباشرة من نفس الطلب الحقيقي: الزبون
     * صاحب الطلب، ومقدم الخدمة يلي نفّذه عبر العرض المقبول — مش
     * أشخاص عشوائيين مالهم علاقة بالطلب المُرفق بالشكوى.
     */
    private function buildFromRequest(
        Collection $requestsWithProvider,
        Collection $customerUserIds,
        Collection $providerUserIds
    ): ?array {
        $request = $requestsWithProvider->random();

        $customerUserId = $customerUserIds->get($request->customer_id);
        $providerUserId = $providerUserIds->get($request->acceptedOffer->service_provider_id);

        if (! $customerUserId || ! $providerUserId) {
            return null; // بيانات ناقصة بالسيدرز السابقة، تجاهل بأمان
        }

        // 50% الزبون يشتكي على مقدم الخدمة، 50% العكس — بس دايماً
        // دورين مختلفين، أبداً نفس الدور.
        [$complainant, $against] = rand(0, 1) === 0
            ? [$customerUserId, $providerUserId]
            : [$providerUserId, $customerUserId];

        return [
            'user_id'            => $complainant,
            'against_user_id'    => $against,
            'service_request_id' => $request->id,
        ];
    }

    /**
     * شكوى عامة (غير مرتبطة بطلب معيّن). المشتكي ممكن يكون زبون أو
     * مقدم خدمة عشوائياً، لكن لو فيها against_user_id، لازم تكون
     * من الدور المعاكس تماماً — هذا بالضبط الشرط يلي كان ناقص بالسيدر
     * الأصلي وسبب المشكلة يلي لاحظتيها.
     */
    private function buildGeneral(Collection $customerUserIds, Collection $providerUserIds): array
    {
        $complainantIsCustomer = rand(0, 1) === 0;

        $complainant = $complainantIsCustomer ? $customerUserIds->random() : $providerUserIds->random();

        $hasAgainstUser = rand(1, 100) <= 70;

        // الطرف التاني دايماً من الدور المعاكس تماماً لدور المشتكي
        $against = $hasAgainstUser
            ? ($complainantIsCustomer ? $providerUserIds->random() : $customerUserIds->random())
            : null;

        return [
            'user_id'            => $complainant,
            'against_user_id'    => $against,
            'service_request_id' => null,
        ];
    }

    private function adminNoteFor(string $status): string
    {
        return $status === 'resolved'
            ? 'تم التواصل مع الطرفين وحل المشكلة وديّاً.'
            : 'تم رفض الشكوى لعدم كفاية الأدلة المقدمة.';
    }
}
