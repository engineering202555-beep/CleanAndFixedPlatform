<?php

namespace App\Observers;

use App\Events\OfferAccepted;
use App\Events\OfferSuperseded;
use App\Models\Offer;
use App\Services\Offer\OfferSupersessionService;
use Illuminate\Support\Facades\DB;

class OfferObserver
{
    public function __construct(
        private readonly OfferSupersessionService $supersessionService
    ) {
    }

    /**
     * "وضح Contract خارجي بدل تعديل Customer": هذا الـ Observer
     * بيلتقط أي تغيير على Offer::status بغض النظر مين نفّذه فعلياً
     * (بما فيهم Customer's OfferController::acceptOffer() الحالي،
     * يلي ممنوع نلمسه). لحظة ما status يصير 'accepted' من أي مصدر،
     * هالمنطق بيشتغل تلقائياً — صفر تنسيق يدوي مطلوب من جهة Customer.
     *
     * المنطق الفعلي (رفض الباقي) بـ OfferSupersessionService، مش هون
     * — الـ Observer بس بيكتشف ويفوّض.
     */
    /**
     * "وضح Contract خارجي بدل تعديل Customer": هذا الـ Observer
     * بيلتقط أي تغيير على Offer::status بغض النظر مين نفّذه فعلياً
     * (بما فيهم Customer's OfferController::acceptOffer() الحالي،
     * يلي ممنوع نلمسه). لحظة ما status يصير 'accepted' من أي مصدر،
     * هالمنطق بيشتغل تلقائياً — صفر تنسيق يدوي مطلوب من جهة Customer.
     *
     * DB::afterCommit() ضروري هون تحديداً: ما عندنا أي سيطرة على
     * حدود الـ Transaction تبع كود Customer (ممكن يكون هالـ update
     * جوّا Transaction عندهم لسا ما التزمت). لو أطلقنا الـ Event
     * فوراً وListener مصفوف (ShouldQueue) أخذه Worker بسرعة، ممكن
     * يقرأ بيانات لسا مش نهائية. afterCommit() بيضمن التنفيذ بعد
     * أقرب Commit فعلي، مهما كان مصدره.
     */
    public function updated(Offer $offer): void
    {
        if (! $offer->wasChanged('status') || $offer->status !== 'accepted') {
            return;
        }

        DB::afterCommit(function () use ($offer) {
            event(new OfferAccepted($offer));

            $rejectedOffers = $this->supersessionService->supersedeOtherOffers($offer);

            foreach ($rejectedOffers as $rejectedOffer) {
                event(new OfferSuperseded($rejectedOffer));
            }
        });
    }
}
