<?php

namespace App\Listeners;

use App\Events\OfferAccepted;
use App\Models\Notification;
use App\Services\Notification\FcmNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleOfferAccepted implements ShouldQueue
{
    public function __construct(
        private readonly FcmNotificationService $fcm
    ) {
    }

    public function handle(OfferAccepted $event): void
    {
        $offer = $event->offer;
        $userId = $offer->serviceProvider->user_id;

        Notification::create([
            'user_id'        => $userId,
            'reference_id'   => $offer->service_request_id,
            'reference_type' => 'service_requests',
            'type'           => 'offer_accepted',
            'title'          => 'تم قبول عرضك',
            'body'           => 'تم قبول عرضك من قبل الزبون، يمكنك متابعة الطلب الآن.',
        ]);

        $this->fcm->sendToUser($userId, 'offer_accepted', [
            'offer_id'           => $offer->id,
            'service_request_id' => $offer->service_request_id,
        ]);
    }
}
