<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\ServiceRequest;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    private const COMMENTS = [
        'تعامل محترف وسريع، أنصح به بشدة.',
        'الخدمة كانت جيدة بس تأخر شوي عن الموعد.',
        'ممتاز، حل المشكلة من أول زيارة.',
        'السعر كان مناسب والعمل نظيف.',
        null, // بعض الزبائن بيقيّمون بدون تعليق نصي
        null,
    ];

    public function run(): void
    {
        $completedRequests = ServiceRequest::query()
            ->where('status', 'completed')
            ->with(['offers' => fn ($query) => $query->where('status', 'accepted')])
            ->get();

        $created = 0;

        foreach ($completedRequests as $request) {
            $acceptedOffer = $request->offers->first();

            // طلب مكتمل بدون عرض مقبول هو تعارض بيانات فعلي، تجاهله
            // بأمان بدل ما يفشل السيدر بالكامل.
            if (! $acceptedOffer) {
                continue;
            }

            Review::create([
                'customer_id' => $request->customer_id,
                'service_request_id' => $request->id,
                'service_provider_id' => $acceptedOffer->service_provider_id,
                'comment' => self::COMMENTS[array_rand(self::COMMENTS)],
                'provider_rating' => $this->weightedRating(),
            ]);

            $created++;
        }

        $this->command->info("تم إنشاء {$created} تقييم للطلبات المكتملة.");
    }

    /**
     * توزيع واقعي للتقييمات: أغلبها إيجابية (4-5)، وبعضها متوسط
     * أو سلبي، مش توزيع عشوائي متساوي بين 1 و5.
     */
    private function weightedRating(): int
    {
        $roll = rand(1, 100);

        return match (true) {
            $roll <= 55 => 5,
            $roll <= 80 => 4,
            $roll <= 92 => 3,
            $roll <= 98 => 2,
            default => 1,
        };
    }
}
