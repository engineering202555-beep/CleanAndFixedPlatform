<?php

namespace App\Services\Offer;

use App\Models\Offer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class OfferSupersessionService
{
    /**
     * تُستدعى لحظة ما عرض معيّن يصير accepted (بغض النظر مين نفّذ
     * القبول فعلياً — Customer's OfferController، خارج نطاقنا).
     * كل باقي العروض pending لنفس الطلب بترفض تلقائياً، بضمان
     * lockForUpdate عشان لو صار قبول عرضين بنفس اللحظة بالغلط (Bug
     * بجانب Customer مثلاً)، ما نرفض عرض اتقبل فعلياً بالغلط.
     *
     * @return Collection<Offer> العروض يلي انرفضت (لإرسال FCM لكل وحدة منهم)
     */
    public function supersedeOtherOffers(Offer $acceptedOffer): Collection
    {
        return DB::transaction(function () use ($acceptedOffer) {
            $siblingOffers = Offer::query()
                ->where('service_request_id', $acceptedOffer->service_request_id)
                ->where('id', '!=', $acceptedOffer->id)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->get();

            foreach ($siblingOffers as $offer) {
                $offer->update(['status' => 'rejected']);
            }

            return $siblingOffers;
        });
    }
}
