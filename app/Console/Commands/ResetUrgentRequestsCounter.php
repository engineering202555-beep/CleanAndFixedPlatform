<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Customer;
class ResetUrgentRequestsCounter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-urgent-requests-counter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
   public function handle(): int
{
    Customer::query()->update([
        'counter_urgent_requests_during_day' => 0,
    ]);

    $this->info('Urgent request counters reset successfully.');

    return self::SUCCESS;
}
}
