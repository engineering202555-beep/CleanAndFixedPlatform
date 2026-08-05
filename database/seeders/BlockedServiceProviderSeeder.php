<?php

namespace Database\Seeders;

use App\Models\BlockedServiceProvider;
use App\Models\Customer;
use App\Models\ServiceProvider;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BlockedServiceProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    /**
     * عدد علاقات الحظر التي سيتم إنشاؤها.
     */
    private const BLOCKS_COUNT = 15;

    public function run(): void
    {
        $customers = Customer::pluck('id');
        $providers = ServiceProvider::where('account_status', 'active')->pluck('id');

        if ($customers->isEmpty() || $providers->isEmpty()) {
            $this->command->warn(
                'يجب وجود زبائن ومقدمي خدمة مقبولين قبل تشغيل هذا السيدر.'
            );

            return;
        }

        $created = 0;

        while ($created < self::BLOCKS_COUNT) {

            $customerId = $customers->random();
            $providerId = $providers->random();

            // لا تنشئ نفس الحظر مرتين
            $exists = BlockedServiceProvider::where('customer_id', $customerId)
                ->where('service_provider_id', $providerId)
                ->exists();

            if ($exists) {
                continue;
            }

            BlockedServiceProvider::create([
                'customer_id' => $customerId,
                'service_provider_id' => $providerId,
            ]);

            $created++;
        }

        $this->command->info("تم إنشاء {$created} علاقة حظر بين الزبائن ومقدمي الخدمة.");
    }

}
