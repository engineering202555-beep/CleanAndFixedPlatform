<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ServiceRequest;
class CancelExpiredServiceRequests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'requests:cancel-expired-service-requests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel expired service requests that received no offers';


    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $count = ServiceRequest::query()
            ->whereIn('status', [
                'pending_local',
                'pending_global',
            ])
            ->where('expires_at', '<=', now())
            ->whereDoesntHave('offers')
            ->update([
                'status' => 'cancel_by_system',
                'updated_at' => now(),
            ]);

        $this->info(
            "Cancelled {$count} expired service requests."
        );

        return self::SUCCESS;
    }
}
