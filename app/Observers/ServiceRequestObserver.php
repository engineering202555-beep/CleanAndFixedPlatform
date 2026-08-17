<?php

namespace App\Observers;

use App\Jobs\NotifyEligibleProvidersJob;
use App\Models\ServiceRequest;

class ServiceRequestObserver
{
    /**
     * سطر واحد بس: تفويض فوري لـ Job بالخلفية. صفر استعلام، صفر
     * منطق أهلية هون — عشان استجابة Customer (يلي بينشئ الطلب أصلاً)
     * تضل سريعة بغض النظر عن عدد مقدمي الخدمة يلي لازم يتفحصوا.
     */
    public function created(ServiceRequest $serviceRequest): void
    {
        NotifyEligibleProvidersJob::dispatch($serviceRequest->id);
    }
}

