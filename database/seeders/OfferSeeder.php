<?php

namespace Database\Seeders;

use App\Models\Offer;
use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class OfferSeeder extends Seeder
{
    private const NOTES = [
        'يمكن الحضور بنفس اليوم صباحاً.',
        'يشمل السعر قطع الغيار الأساسية.',
        'السعر تقريبي وقد يتغير بعد الكشف الفعلي.',
        'متوفر خلال 24 ساعة القادمة.',
    ];

    private Collection $providers;

    public function run(): void
    {
        $this->providers = ServiceProvider::query()
            ->where('account_status', 'active')
            ->get();

        if ($this->providers->isEmpty()) {
            $this->command->warn('لازم يكون في مقدمي خدمة مقبولين (approved) قبل تشغيل هذا السيدر.');

            return;
        }

        $totalOffers = 0;

        ServiceRequest::query()->each(function (ServiceRequest $request) use (&$totalOffers) {
            $totalOffers += $this->createOffersFor($request);
        });

        $this->command->info("تم إنشاء {$totalOffers} عرض توزّعت على طلبات الخدمة حسب حالة كل طلب.");
    }

    /**
     * كل نوع حالة بطلب الخدمة بيولّد نمط عروض مختلف منطقياً:
     * - لسا عم يبحث (pending_*) → صفر عروض.
     * - processing → عدة عروض pending (منافسة، الزبون لسا ما قرر).
     * - accepted/scheduled/in_progress/completed → عرض واحد accepted
     *   + الباقي rejected تلقائياً (نفس منطق قبول عرض واحد بس).
     * - inspection_* → عرض accepted بسعر/مدة null (لسا ما انكشف العطل).
     * - rejected → كل العروض rejected (الزبون ما اختار).
     * - cancel_* → بعض العروض rejected أو بدون عروض أصلاً حسب مرحلة الإلغاء.
     */
    private function createOffersFor(ServiceRequest $request): int
    {
        return match ($request->status) {
            'pending_local', 'pending_global' => 0,

            'processing' => $this->createCompetingOffers($request, rand(2, 4)),

            'accepted', 'scheduled', 'in_progress', 'completed' =>
            $this->createDecidedOffers($request, acceptedHasSchedule: true),

            'inspection_accepted', 'inspection_in_progress', 'fault_detected' =>
            $this->createInspectionOffer($request),

            'rejected' => $this->createAllRejectedOffers($request, rand(2, 3)),

            'cancel_by_customer', 'cancel_by_provider' =>
            rand(0, 1) ? $this->createAllRejectedOffers($request, rand(1, 2)) : 0,

            'cancel_by_system' => rand(0, 1)
                ? $this->createCompetingOffers($request, rand(2, 3))
                : $this->createDecidedOffers($request, acceptedHasSchedule: false),

            default => 0,
        };
    }

    private function createCompetingOffers(ServiceRequest $request, int $count): int
    {
        // بعض دفعات العروض "قديمة" (منتهية صلاحية) لتختبر تحويل
        // processing لـ cancel_by_system تلقائياً بعد مهلة معينة.
        $isStale = rand(1, 100) <= 25;

        for ($i = 0; $i < $count; $i++) {
            $this->makeOffer($request, $this->pickProvider(), 'pending', $isStale);
        }

        return $count;
    }

    private function createDecidedOffers(ServiceRequest $request, bool $acceptedHasSchedule): int
    {
        $providers = $this->providers->random(min(rand(2, 3), $this->providers->count()));
        $accepted = false;
        $count = 0;

        foreach ($providers as $provider) {
            $status = ! $accepted ? 'accepted' : 'rejected';
            $this->makeOffer($request, $provider, $status, false, $acceptedHasSchedule && $status === 'accepted');
            $accepted = true;
            $count++;
        }

        return $count;
    }

    private function createInspectionOffer(ServiceRequest $request): int
    {
        Offer::create([
            'service_provider_id' => $this->pickProvider()->id,
            'service_request_id' => $request->id,
            'price' => null, // لسا ما انكشف العطل فعلياً
            'estimated_duration' => null,
            'status' => 'accepted',
            'notes' => 'سعر الكشف فقط، سعر التصليح يُحدد بعد الكشف الفعلي.',
            'starts_at' => now()->addHours(rand(1, 24)),
            'duration_in_minutes' => 60,
            'expires_at' => now()->addMinutes(30),
        ]);

        return 1;
    }

    private function createAllRejectedOffers(ServiceRequest $request, int $count): int
    {
        for ($i = 0; $i < $count; $i++) {
            $this->makeOffer($request, $this->pickProvider(), 'rejected', false);
        }

        return $count;
    }

    private function makeOffer(
        ServiceRequest $request,
        ServiceProvider $provider,
        string $status,
        bool $isStale,
        bool $futureSchedule = false
    ): void {
        $createdAt = $isStale ? now()->subHour() : now()->subMinutes(rand(1, 20));

        Offer::create([
            'service_provider_id' => $provider->id,
            'service_request_id' => $request->id,
            'price' => rand(20, 150),
            'estimated_duration' => [30, 60, 90, 120][array_rand([30, 60, 90, 120])],
            'status' => $status,
            'notes' => self::NOTES[array_rand(self::NOTES)],
            'starts_at' => $futureSchedule ? now()->addDays(rand(1, 3)) : now()->subHours(rand(1, 48)),
            'duration_in_minutes' => 30,
            'expires_at' => $createdAt->copy()->addMinutes(30),
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }

    private function pickProvider(): ServiceProvider
    {
        return $this->providers->random();
    }
}
