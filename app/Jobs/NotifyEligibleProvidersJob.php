<?php

namespace App\Jobs;

use App\Events\NewServiceRequestEligible;
use App\Models\ServiceRequest;
use App\Services\ServiceRequest\ProviderEligibilityService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyEligibleProvidersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly int $serviceRequestId
    ) {
    }

    public function handle(ProviderEligibilityService $eligibilityService): void
    {
        $request = ServiceRequest::find($this->serviceRequestId);

        if (! $request) {
            return; // انحذف أو انلغى قبل ما الـ Job يتنفذ، تجاهل بأمان
        }

        $eligibleProviders = $eligibilityService->getEligibleProviders($request);

        foreach ($eligibleProviders as $provider) {
            event(new NewServiceRequestEligible($request, $provider));
        }
    }
}
