<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProviderAccountActive
{
    /**
     * يتحقق بكل طلب محمي (مش بس لحظة login) إنه account_status
     * لسا active فعلياً. لو الأدمن حظر/رفض مقدم خدمة عنده توكن
     * صالح مسبقاً، هاد الميدلوير بيقطع وصوله فوراً بأول طلب تالي،
     * بدل ما يضل التوكن القديم شغّال لحد ما ينتهي/يُحذف يدوياً.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $provider = $request->user()?->serviceProvider;

        if (! $provider || $provider->account_status !== 'active') {
            // إبطال التوكن الحالي فوراً — مش بس رفض الطلب هذا، منع
            // أي طلب تاني بنفس التوكن مستقبلاً.
            $request->user()?->currentAccessToken()?->delete();

            return ApiResponse::error(
                'حسابك لم يعد نشطاً حالياً، يرجى مراجعة حالة حسابك.',
                403
            );
        }

        return $next($request);
    }
}
