<?php

namespace App\Services\Customer;
use App\Models\PhoneOtp;
use Illuminate\Validation\ValidationException;
use App\Services\Customer\WhatsApp\UltraMsgService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Contracts\Customer\OTPServiceInterface;
use App\Models\User;

class OTPService implements OTPServiceInterface
{

protected UltraMsgService $whatsApp;

public function __construct(UltraMsgService $whatsApp)
{
    $this->whatsApp = $whatsApp;
}



    public function generate(User $user): void
{
    // حذف أي OTP قديم وغير مستخدم
    PhoneOtp::where('user_id', $user->id)
        ->where('verified', false)
        ->delete();

    // إنشاء كود مكون من 6 أرقام
    $code = random_int(100000, 999999);

    // حفظ الكود
    PhoneOtp::create([
        'user_id'    => $user->id,
        'phone'      => $user->phone_number,
        'code'       => $code,
        'expires_at' => Carbon::now()->addMinutes(5),
        'verified'   => false,
    ]);

    // لاحقًا سنرسل الكود عبر WhatsApp

      $message = "رمز التحقق الخاص بك هو: {$code}\nصالح لمدة 5 دقائق.";

if (config('services.otp.driver') === 'whatsapp') {

    $this->whatsApp->send(
        $user->phone_number,
        $message
    );

} 

else {

    logger()->info("OTP for {$user->phone_number}: {$code}");

}



}

   public function verify(string $phone, string $code): bool
{
    DB::transaction(function () use ($phone, $code) {

        // البحث عن المستخدم
        $user = User::where('phone_number', $phone)->firstOrFail();

        // البحث عن آخر OTP صالح
        $otp = PhoneOtp::where('user_id', $user->id)
            ->where('phone', $phone)
            ->where('code', $code)
            ->where('verified', false)
            ->latest()
            ->first();

        // إذا لم يوجد
        if (!$otp) {
      throw ValidationException::withMessages([
    'otp' => ['رمز التحقق غير صحيح.']
]);
        }

        // التحقق من انتهاء الصلاحية
        if ($otp->expires_at->isPast()) {
           throw ValidationException::withMessages([
    'otp' => ['انتهت صلاحية رمز التحقق.']
]); }

        // تحديث حالة OTP
        $otp->update([
            'verified' => true,
        ]);

        // تحديث المستخدم
        $user->update([
            'phone_verified_at' => now(),
        ]);

        // حذف جميع الأكواد القديمة
        PhoneOtp::where('user_id', $user->id)
            ->where('id', '!=', $otp->id)
            ->delete();
    });

    return true;
}

    public function resend(User $user): void
    {
$this->generate($user);
    }
}