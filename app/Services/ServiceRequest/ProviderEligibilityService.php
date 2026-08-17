<?php

namespace App\Services\ServiceRequest;

use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use App\Traits\ChecksProviderAvailability;
use Illuminate\Database\Eloquent\Collection;

class ProviderEligibilityService
{
    use ChecksProviderAvailability;

    private const LOCAL_SEARCH_STATUSES = ['pending_local'];
    private const GLOBAL_SEARCH_STATUSES = ['pending_global'];

    public function isEligible(ServiceProvider $provider, ServiceRequest $request): bool
    {
        if ($provider->account_status !== 'active') {
            return false;
        }

        if ($provider->availability_status === 'offline') {
            return false;
        }

        if ($provider->service_category_id !== $request->service_category_id) {
            return false;
        }

        if (! $this->matchesArea($provider, $request)) {
            return false;
        }

        // العاجل بيتجاوز كل شي (الدوام وDND معاً) — بغض النظر عن أي إعداد.
        if ($request->is_urgent) {
            return true;
        }

        // العادي: الوقت بس بيأثر لو DND مفعّل فعلياً. DND=false يعني
        // الوقت مالوش أي تأثير إطلاقاً على الطلبات العادية.
        if ($provider->do_not_disturb && ! $this->isWithinWorkingHours($provider)) {
            return false;
        }

        return true;
    }

    /**
     * pending_local: نفس service_area_id بالضبط (بحث ضيّق).
     * pending_global: نفس المدينة (city) بس، نطاق أوسع بيتحمّل
     * تكاليف إضافية (نقل أبعد) — مش نفس المنطقة بالضبط.
     * أي حالة تانية (processing، الخ): تطابق مباشر افتراضي (نادراً
     * ما توصل هون أصلاً لأنه الأهلية بتُفحص بس بمرحلة البحث).
     */
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
            ->where('availability_status', '!=', 'offline')
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

