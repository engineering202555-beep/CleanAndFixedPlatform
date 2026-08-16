<?php

namespace App\Services\Auth;

use App\Models\ServiceProvider;
use App\Models\User;
use App\Services\Image\ImageUploadService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ProviderRegistrationService
{
    public function __construct(
        private readonly OtpService $otpService,
        private readonly ImageUploadService $imageUploadService,
    ) {
    }

    /**
     * كل شي بجدولين (users + service_providers) + رفع الوثائق داخل
     * Transaction واحدة — إما الكل نجح أو ولا شي اتسجّل. إرسال الـ
     * OTP نفسه *خارج* الـ Transaction بالقصد (استدعاء HTTP خارجي،
     * ما بدنا نحجز قفل على الصفوف طول ما بينتظر رد UltraMsg).
     */
    public function register(array $data): User
    {
        $plainCode = null;

        try {
            $user = DB::transaction(function () use ($data, &$plainCode) {
                $user = User::create([
                    'first_name'   => $data['first_name'],
                    'last_name'    => $data['last_name'],
                    'phone_number' => $data['phone_number'],
                    'password'     => Hash::make($data['password']),
                ]);

                $user->assignRole('provider');

                $provider = ServiceProvider::create([
                    'user_id'             => $user->id,
                    'service_category_id' => $data['service_category_id'],
                    'service_area_id'     => $data['service_area_id'],
                    'inspection_price'    => $data['inspection_price'],
                    'bio'                 => $data['bio'] ?? null,
                    'experience_years'    => $data['experience_years'],
                    'working_from'        => $data['working_from'],
                    'working_to'          => $data['working_to'],
                    'latitude'            => $data['latitude'],
                    'longitude'           => $data['longitude'],
                    'account_status'      => 'pending',
                    'availability_status' => 'offline',
                ]);

                $this->imageUploadService->upload($provider, $data['profile_image'], 'profile');
                $this->imageUploadService->upload($provider, $data['id_document_front'], 'documents');
                $this->imageUploadService->upload($provider, $data['id_document_back'], 'documents');
                $this->imageUploadService->upload($provider, $data['profession_document'], 'documents');

                // إنشاء صف الـ OTP جوّا نفس الـ Transaction — لو فشل
                // لأي سبب (حجم عمود، قيد قاعدة بيانات، أي شي)، كل
                // التسجيل بالكامل بيترجع سوا (Rollback)، ما يضل
                // User/ServiceProvider محفوظين بدون OTP صالح.
                $plainCode = $this->otpService->generateAndStore($user);

                return $user;
            });
        } catch (QueryException $e) {
            throw new ConflictHttpException('رقم الهاتف مسجّل مسبقاً.');
        }

        // الإرسال الفعلي (اتصال خارجي) برّا الـ Transaction، بعد ما
        // نضمن إنه كل شي محفوظ صح بقاعدة البيانات.
        $this->otpService->dispatch($user, $plainCode);

        return $user;

    }
}

