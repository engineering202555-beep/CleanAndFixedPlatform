<?php

namespace App\Console\Commands;

use App\Models\Customer;
use Illuminate\Console\Command;

class UnblockExpiredCustomers extends Command
{



    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'customers:unblock-expired';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'إرجاع الزبائن المحظورين تلقائياً لحالة active بعد انتهاء مدة الحظر';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = Customer::query()
            ->where('status', 'blocked')
            ->whereNotNull('blocked_until')
            ->where('blocked_until', '<=', now())
            ->update([
                'status' => 'active',
                'block_reason' => null,
                'blocked_until' => null,
            ]);

        $this->info("تم فك الحظر تلقائياً عن {$count} زبون.");
    }
}
