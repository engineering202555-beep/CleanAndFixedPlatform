<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ProviderAuthService
{
    /**
     * هون بالضبط بوابة الحماية الحقيقية يلي طلبتيها (Enforced على
     * الـ Backend، مش الفرونت إند فقط): التوكن ما بينصدر إطلاقاً
     * إلا بعد التحقق من الشرطين معاً (توثيق الهاتف + موافقة الأدمن)،
     * مش بعدين عبر Middleware على راوتات لاحقة.
     */
    public function login(array $credentials): array
    {
        $user = User::query()
            ->where('phone_number', $credentials['phone_number'])
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw new HttpException(401, 'بيانات الدخول غير صحيحة.');
        }

        if (! $user->phone_verified_at) {
            throw new ConflictHttpException('رقم الهاتف غير موثّق بعد، يرجى إكمال التحقق أولاً.');
        }

        $provider = $user->serviceProvider;

        if (! $provider) {
            throw new HttpException(401, 'هذا الحساب ليس حساب مقدم خدمة.');
        }

        $this->assertAccountUsable($provider);

        $token = $user->createToken('provider-app')->plainTextToken;

        return [
            'user'     => $user,
            'provider' => $provider,
            'token'    => $token,
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }

    private function assertAccountUsable(\App\Models\ServiceProvider $provider): void
    {
        match ($provider->account_status) {
            'active'   => null,
            'pending'  => throw new ConflictHttpException('حسابك لسا بانتظار موافقة الإدارة.'),
            'rejected' => throw new ConflictHttpException(
                'تم رفض طلب انضمامك. السبب: '.($provider->rejection_reason ?? 'غير محدد')
            ),
            'blocked'  => throw new ConflictHttpException(
                'حسابك محظور حالياً. السبب: '.($provider->block_reason ?? 'غير محدد')
            ),
        };
    }
}

