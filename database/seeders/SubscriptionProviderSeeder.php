<?php

namespace Database\Seeders;

use App\Models\ServiceProvider;
use App\Models\SubscriptionProvider;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubscriptionProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $providers = ServiceProvider::where('is_approved', true)->get();

        foreach ($providers as $index => $provider) {

            if ($index < 6) {
                $subscriptionId = 1; // Free
            } elseif ($index < 11) {
                $subscriptionId = 2; // Silver
            } else {
                $subscriptionId = 3; // Gold
            }

            if ($subscriptionId == 1) {

                $usedRequests = rand(0, 3);

            } elseif ($subscriptionId == 2) {

                $usedRequests = rand(0, 100);

            } else {

                $usedRequests = rand(0, 120);
            }

            SubscriptionProvider::create([

                'service_provider_id' => $provider->id,
                'subscription_id' => $subscriptionId,

                'starts_at' => Carbon::now(),

                'ends_at' => Carbon::now()->addDays(30),

                'status' => 'active',

                'used_requests' => $usedRequests,
            ]);
        }
    }
}
