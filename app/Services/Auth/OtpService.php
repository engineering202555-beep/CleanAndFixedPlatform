<?php

namespace App\Services\Auth;

use App\Models\PhoneOtp;
use App\Models\User;
use App\Support\Otp\OtpGatewayInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class OtpService
{
    private const CODE_LENGTH = 6;
    private const EXPIRY_MINUTES = 10;
    private const COOLDOWN_SECONDS = 60;
    private const MAX_PER_WINDOW = 3;
    private const WINDOW_MINUTES = 15;

    public function __construct(
        private readonly OtpGatewayInterface $gateway
    ) {
    }

    /**
     * الاستخدام العادي (Resend، أو أي استدعاء مستقل مش جوّا Transaction
     * تسجيل): يعمل الاثنين سوا (تخزين + إرسال) بترتيبهم الصحيح.
     */
    public function send(User $user): void
    {
        $plainCode = DB::transaction(fn () => $this->generateAndStore($user));

        $this->dispatch($user, $plainCode);
    }

    /**
     * بس "إنشاء الصف" (INSERT بحت، بدون أي اتصال خارجي) — مصممة
     * عشان تنّادى من جوّا Transaction تسجيل موجودة مسبقاً (متل
     * ProviderRegistrationService)، عشان لو فشلت لأي سبب (متل باج
     * حجم العمود يلي صار)، كل التسجيل يترجع (Rollback) سوا، مش يضل
     * User/ServiceProvider محفوظين بدون OTP صالح.
     *
     * لاحظي: ما فيها DB::transaction() لحالها هون — المسؤولية عن
     * فتح/إغلاق الـ Transaction على الجهة المستدعية (Caller).
     */
    public function generateAndStore(User $user): string
    {
        $this->assertNotRateLimited($user);

        $plainCode = str_pad((string) random_int(0, 999999), self::CODE_LENGTH, '0', STR_PAD_LEFT);

        PhoneOtp::query()
            ->where('user_id', $user->id)
            ->where('verified', false)
            ->update(['expires_at' => now()]);

        PhoneOtp::create([
            'user_id'    => $user->id,
            'phone'      => $user->phone_number,
            'code'       => Hash::make($plainCode),
            'expires_at' => now()->addMinutes(self::EXPIRY_MINUTES),
            'verified'   => false,
        ]);

        return $plainCode;
    }

    /**
     * الاتصال الخارجي الفعلي (HTTP لـ UltraMsg) — لازم يضل برّا أي
     * Transaction دايماً (هذا كان صحيح بالتصميم الأصلي، المشكلة
     * كانت بس بمكان إنشاء الصف مش هون).
     */
    public function dispatch(User $user, string $plainCode): void
    {
        $sent = $this->gateway->send($user->phone_number, $plainCode);

        if (! $sent) {
            throw new UnprocessableEntityHttpException(
                'تعذّر إرسال رمز التحقق حالياً، حاول مرة أخرى بعد قليل.'
            );
        }
    }

    public function verify(User $user, string $code): void
    {
        DB::transaction(function () use ($user, $code) {
            $otp = PhoneOtp::query()
                ->where('user_id', $user->id)
                ->where('verified', false)
                ->where('expires_at', '>', now())
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (! $otp || ! Hash::check($code, $otp->code)) {
                throw new UnprocessableEntityHttpException('رمز التحقق غير صحيح أو منتهي الصلاحية.');
            }

            $otp->update(['verified' => true]);
            $user->update(['phone_verified_at' => now()]);
        });
    }

    private function assertNotRateLimited(User $user): void
    {
        $lastOtp = PhoneOtp::query()
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();

        if ($lastOtp && $lastOtp->created_at->diffInSeconds(now()) < self::COOLDOWN_SECONDS) {
            $remaining = self::COOLDOWN_SECONDS - $lastOtp->created_at->diffInSeconds(now());

            throw new ConflictHttpException("يرجى الانتظار {$remaining} ثانية قبل طلب رمز جديد.");
        }

        $recentCount = PhoneOtp::query()
            ->where('user_id', $user->id)
            ->where('created_at', '>=', now()->subMinutes(self::WINDOW_MINUTES))
            ->count();

        if ($recentCount >= self::MAX_PER_WINDOW) {
            throw new ConflictHttpException(
                'تم تجاوز الحد الأقصى لطلبات رمز التحقق، حاول مرة أخرى بعد '.self::WINDOW_MINUTES.' دقيقة.'
            );
        }
    }
}
