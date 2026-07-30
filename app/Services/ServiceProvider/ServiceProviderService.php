<?php

namespace App\Services\ServiceProvider;

use App\Filters\ServiceProviderFilter;
use App\Models\ServiceProvider;
use App\Models\Subscription;
use App\Models\SubscriptionProvider;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ServiceProviderService
{
    public function getApprovedProviders(): LengthAwarePaginator
    {
        return ServiceProvider::query()
            ->where('account_status', 'active')
            ->with([
                'user:id,first_name,last_name',
                'serviceCategory:id,name',
                'serviceArea:id,city,area_name',
                'profileImage',
            ])
            ->latest()
            ->paginate();
    }

    public function getApprovedProvidersFilter(array $filters = []): LengthAwarePaginator
    {
        $query = ServiceProvider::query()
            ->where('account_status', 'active')
            ->with([
                'user:id,first_name,last_name',
                'serviceCategory:id,name',
                'serviceArea:id,city,area_name',
                'profileImage',
            ]);

        (new ServiceProviderFilter($filters))->apply($query);

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function getApprovedProviderDetails(ServiceProvider $provider): ServiceProvider
    {
        if ($provider->account_status !== 'active') {
            throw new NotFoundHttpException('Service provider not found.');
        }

        return $provider->load([
            'user',
            'serviceCategory',
            'serviceArea',
            'images',
            'subscriptions.subscription',
        ]);
    }

    public function getPendingProviders(): LengthAwarePaginator
    {
        return ServiceProvider::query()
            ->where('account_status', 'pending')
            ->with([
                'user',
                'serviceCategory',
                'serviceArea',
                'images',
            ])
            ->oldest()
            ->paginate();
    }

    public function getRejectedProviders(): LengthAwarePaginator
    {
        return ServiceProvider::query()
            ->where('account_status', 'rejected')
            ->with([
                'user',
                'serviceCategory',
                'serviceArea',
                'images',
            ])
            ->latest('updated_at')
            ->paginate();
    }

    public function getBlockedProviders(): LengthAwarePaginator
    {
        return ServiceProvider::query()
            ->where('account_status', 'blocked')
            ->with([
                'user:id,first_name,last_name,phone_number',
                'serviceCategory:id,name',
                'serviceArea:id,city,area_name',
            ])
            ->orderBy('blocked_until')
            ->paginate();
    }

    public function getMostActiveThisMonth(int $limit = 10): Collection
    {
        return ServiceProvider::query()
            ->where('account_status', 'active')
            ->withCount([
                'offers as completed_requests_this_month' => function ($query) {
                    $query->where('status', 'accepted')
                        ->whereHas('serviceRequest', function ($requestQuery) {
                            $requestQuery->where('status', 'completed')
                                ->whereBetween('updated_at', [
                                    now()->startOfMonth(),
                                    now()->endOfMonth(),
                                ]);
                        });
                },
            ])
            ->with([
                'user:id,first_name,last_name,phone_number',
                'serviceCategory:id,name',
                'serviceArea:id,city,area_name',
            ])
            ->orderByDesc('completed_requests_this_month')
            ->limit($limit)
            ->get();
    }

    public function getMostComplainedAgainst(int $limit = 10): Collection
    {
        return ServiceProvider::query()
            ->where('account_status', 'active')
            ->withCount([
                'complaintsAgainst as complaints_count' => function ($query) {
                    $query->where('status', '!=', 'rejected');
                },
            ])
            ->with([
                'user:id,first_name,last_name,phone_number',
                'serviceCategory:id,name',
                'serviceArea:id,city,area_name',
            ])
            ->having('complaints_count', '>', 0)
            ->orderByDesc('complaints_count')
            ->limit($limit)
            ->get();
    }
}
