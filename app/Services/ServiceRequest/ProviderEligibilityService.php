<?php

namespace App\Services\ServiceRequest;

use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use App\Traits\ChecksProviderAvailability;
use Illuminate\Database\Eloquent\Collection;

class ProviderEligibilityService
{
    use ChecksProviderAvailability;

    private const GLOBAL_SEARCH_STATUSES = ['pending_global'];

    /**
     * ⚠️ لاحظي: availability_status (busy/available/offline) ما
     * عاد يُفحص هون إطلاقاً — أصبح حقل معلوماتي بس (لعرضه بلوحة
     * الأدمن)، مش بوابة أهلية فعلية. القرار الوحيد بمنطقة "الوقت"
     * هو do_not_disturb + الدوام، بغض النظر عن قيمة availability_status.
     */
    public function isEligible(ServiceProvider $provider, ServiceRequest $request): bool
    {
        if ($provider->account_status !== 'active') {
            return false;
        }

        if ($provider->service_category_id !== $request->service_category_id) {
            return false;
        }

        if (! $this->matchesArea($provider, $request)) {
            return false;
        }

        // العاجل بيتجاوز كل شي دايماً — داخل الدوام أو برّاه، DND
        // مفعّل أو لأ.
        if ($request->is_urgent) {
            return true;
        }

        // عادي: يُمنع فقط بحالة واحدة — DND مفعّل وبرّا الدوام سوا.
        // DND مطفي = الوقت مالوش أي تأثير إطلاقاً (حتى لو برّا الدوام).
        if ($provider->do_not_disturb && ! $this->isWithinWorkingHours($provider)) {
            return false;
        }

        return true;
    }

    private function matchesArea(ServiceProvider $provider, ServiceRequest $request): bool
    {
        if (in_array($request->status, self::GLOBAL_SEARCH_STATUSES, true)) {
            return $provider->serviceArea?->city === $request->serviceArea?->city;
        }

        return $provider->service_area_id === $request->service_area_id;
    }

    public function getEligibleProviders(ServiceRequest $request): Collection
    {
        $query = ServiceProvider::query()
            ->with('serviceArea:id,city')
            ->where('account_status', 'active')
            ->where('service_category_id', $request->service_category_id);

        if (in_array($request->status, self::GLOBAL_SEARCH_STATUSES, true)) {
            $request->loadMissing('serviceArea:id,city');
            $city = $request->serviceArea?->city;

            $query->whereHas('serviceArea', function ($q) use ($city) {
                $q->where('city', $city);
            });
        } else {
            $query->where('service_area_id', $request->service_area_id);
        }

        return $query->get()
            ->filter(fn (ServiceProvider $provider) => $this->isEligible($provider, $request))
            ->values();
    }
}
