<?php

namespace App\Services\Complaint;

use App\Models\Complaint;

class ComplaintDetailService
{
    /**
     * تحميل كل شي مرة واحدة (Eager Loading كامل)، بما فيها Lifecycle
     * الطلب المرتبط لو موجود — service_request_id أصلاً Nullable،
     * فكل شي هون لازم يتعامل مع احتمال عدم وجوده بأمان.
     */
    public function getDetails(Complaint $complaint): Complaint
    {
        return $complaint->load([
            'user',
            'againstUser',
            'serviceRequest.customer.user:id,first_name,last_name',
            'serviceRequest.serviceCategory:id,name',
            'serviceRequest.serviceArea:id,area_name,city',
            'serviceRequest.acceptedOffer.serviceProvider.user:id,first_name,last_name',
        ]);
    }
}
