<?php

namespace App\Console\Commands;

use App\Models\ServiceProvider;
use Illuminate\Console\Command;

class UnblockExpiredProviders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'providers:unblock-expired';

    protected $description = 'إرجاع مقدمي الخدمة المحظورين تلقائياً لحالة active بعد انتهاء مدة الحظر';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = ServiceProvider::query()
            ->where('account_status', 'blocked')
            ->whereNotNull('blocked_until')
            ->where('blocked_until', '<=', now())
            ->update([
                'account_status' => 'active',
                'availability_status' => 'offline',
                'block_reason' => null,
                'blocked_until' => null,
            ]);

        $this->info("تم فك الحظر تلقائياً عن {$count} مقدم خدمة.");
    }
}
