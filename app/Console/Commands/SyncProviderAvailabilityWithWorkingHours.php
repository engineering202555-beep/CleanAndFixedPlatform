<?php

namespace App\Console\Commands;

use App\Models\ServiceProvider;
use Illuminate\Console\Command;

class SyncProviderAvailabilityWithWorkingHours extends Command
{
    protected $signature = 'app:sync-provider-availability';

    protected $description = 'تحويل مقدمي الخدمة الخاملين (available) لـ offline تلقائياً خارج دوامهم، والعكس';

    /**
     * استعلامين مجمّعين (Bulk UPDATE) بس، بدون Loop على كل مقدم
     * خدمة لحاله — الأداء نفسه بغض النظر عن عدد الصفوف.
     *
     * ⚠️ بالقصد ما بيلمس busy إطلاقاً (شرط WHERE محصور بـ
     * available/offline بس) — مقدم خدمة عم يشتغل فعلياً ما لازم
     * ينقلب offline بالغلط لمجرد إنه وقت دوامه المعلن خلص وهو
     * لسا بنص شغلة.
     */
    public function handle(): int
    {
        $wentOffline = ServiceProvider::query()
            ->where('availability_status', 'available')
            ->whereRaw('CURTIME() NOT BETWEEN working_from AND working_to')
            ->update(['availability_status' => 'offline']);

        $backOnline = ServiceProvider::query()
            ->where('availability_status', 'offline')
            ->whereRaw('CURTIME() BETWEEN working_from AND working_to')
            ->update(['availability_status' => 'available']);

        $this->info("تم تحويل {$wentOffline} لـ offline، و{$backOnline} رجعوا available.");

        return self::SUCCESS;
    }
}
