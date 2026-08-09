<?php

namespace Database\Seeders;

use App\Models\ServiceProvider;
use App\Models\Subscription;
use App\Models\SubscriptionProvider;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubscriptionProviderSeeder extends Seeder
{
    public function run(): void
    {
        // احذف بيانات الـ seeder السابقة فقط
        SubscriptionProvider::query()->delete();

        $providers = ServiceProvider::query()
            ->where('account_status', 'active')
            ->orderBy('id')
            ->get();

        $subscriptions = Subscription::query()
            ->whereIn('id', [1, 2, 3])
            ->get()
            ->keyBy('id');

        foreach ($providers as $index => $provider) {

            $subscriptionId = match (true) {
                $index < 6 => 1,
                $index < 11 => 2,
                default => 3,
            };

            $subscription = $subscriptions->get($subscriptionId);

            if (! $subscription) {
                continue;
            }

            $status = match ($index % 3) {
                0 => 'active',
                1 => 'pending_payment',
                default => 'cancelled',
            };

            $startsAt = Carbon::now()->subDays(rand(0, 20));

            $endsAt = $startsAt->copy()
                ->addDays($subscription->duration_in_days);

            $usedRequests = rand(
                0,
                $subscription->requests_per_month
            );

            SubscriptionProvider::create([
                'service_provider_id' => $provider->id,
                'subscription_id' => $subscription->id,

                'starts_at' => $startsAt,
                'ends_at' => $endsAt,

                'status' => $status,

                'used_requests' => $usedRequests,

                // Snapshot
                'requests_limit' => $subscription->requests_per_month,
                'price_paid' => $subscription->price,

                'is_complimentary' => false,
            ]);
        }
    }
}
