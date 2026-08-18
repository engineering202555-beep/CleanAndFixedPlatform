<?php

namespace App\Listeners;

use App\Events\NewServiceRequestEligible;
use App\Services\Notification\FcmNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNewServiceRequestFcm implements ShouldQueue
{
    public function __construct(
        private readonly FcmNotificationService $fcm
    ) {
    }

    /**
     * queue() بيحدد اسم قائمة الانتظار قبل حتى ما الـ Job ينحط
     * بالـ Database — لو شغّلتي Worker بالأولوية الصحيحة:
     *   php artisan queue:work --queue=urgent,default
     * الطلبات العاجلة بتتعالج قبل أي Job عادي متراكم بالطابور،
     * حتى لو انضافت بعده زمنياً. هذا بالضبط أسلوب Uber/Careem
     * بالتعامل مع أولوية التوصيل، بدون أي عمود جديد بقاعدة البيانات
     * — الأولوية هون خاصية بمستوى الـ Queue، مش بمستوى الـ Schema.
     */
    public function queue(NewServiceRequestEligible $event): string
    {
        return $event->serviceRequest->is_urgent ? 'urgent' : 'default';
    }

    public function handle(NewServiceRequestEligible $event): void
    {
        $this->fcm->sendToUser(
            $event->provider->user_id,
            'new_service_request',
            [
                'service_request_id' => $event->serviceRequest->id,
                'is_urgent'           => $event->serviceRequest->is_urgent,
            ],
            highPriority: $event->serviceRequest->is_urgent,
        );
    }
}
