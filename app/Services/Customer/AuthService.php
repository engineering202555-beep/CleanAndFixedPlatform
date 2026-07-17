<?php
namespace App\Services\Customer;

use App\Models\User;
use Illuminate\Validation\ValidationException;
use App\Events\UserRegistered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use App\Models\Customer;


use App\Contracts\Customer\OTPServiceInterface;
class AuthService
{
    public function __construct(
    private OTPServiceInterface $otpService
) {}
    public function register(array $data): array
    {
     return DB::transaction(function () use ($data) {

    $user = User::create([
        'first_name'   => $data['first_name'],
        'last_name'    => $data['last_name'],
        'phone_number' => $data['phone_number'],
        'password'     => Hash::make($data['password']),
    ]);

    $user->assignRole('customer');

    $customer=Customer::create([
        'user_id' => $user->id,
       // 'service_area_id' => $data['service_area_id'],
        'status' => 'active',
        'blocked_until' => null,
    ]);

    $this->otpService->generate($user);

    return [
        'message' => 'Account created successfully. Please verify your phone number.',
        'user' => $user,
        'customer'=> $customer
    ];
});
    }

public function verifyOtp(array $data): array
{
    $this->otpService->verify(
        $data['phone_number'],
        $data['code']
    );

    return [
        'message' => 'Phone verified successfully.'
    ];
}
    public function login(array $data):array
    {

      $user=User::where('phone_number',$data['phone_number'])->first();
      if( !$user){
throw ValidationException::withMessages(['phone_number'=>['رقم الهاتف خاطئ او كلمة المرور غير صجيجة'] ]);
      }
if(!Hash::check($data['password'],$user->password)){
    throw ValidationException::withMessages(['password'=>['رقم الهاتف خاطئ او كلمة المرور غير صجيجة'] ]);
}
if (is_null($user->phone_verified_at)) {
    throw ValidationException::withMessages([
        'phone_number' => ['Please verify your phone number first.']
    ]);
}
   $token = $user
        ->createToken('mobile')
        ->plainTextToken;

return [
        'user' => $user,
        'token' => $token,
    ];


    }


    public function logout(User $user):void
    {
$user->currentAccessToken()->delete();
    }

public function resendOtp(string $phoneNumber): array
{
    $user = User::where('phone_number', $phoneNumber)->first();

    if (!$user) {
        throw ValidationException::withMessages([
            'phone_number' => ['رقم الهاتف غير موجود.']
        ]);
    }

    if ($user->phone_verified_at) {
        throw ValidationException::withMessages([
            'phone_number' => ['تم التحقق من رقم الهاتف مسبقًا.']
        ]);
    }

    $this->otpService->generate($user);

    return [
        'message' => 'تم إرسال رمز تحقق جديد.'
    ];
   
}


public function forgetPassword(array $data): array
{

$user = User::where(
    'phone_number',
    $data['phone_number']
)->first();

if(!$user){

    throw ValidationException::withMessages([

        'phone_number'=>['رقم الهاتف غير موجود.']

    ]);

}
$this->otpService->generate($user);
return [

    'message'=>'تم إرسال رمز إعادة تعيين كلمة المرور.'

];



}


public function resetPassword(array $data): array
{
    $user = User::where(
        'phone_number',
        $data['phone_number']
    )->first();

    if (!$user) {

        throw ValidationException::withMessages([

            'phone_number' => ['رقم الهاتف غير موجود.']

        ]);

    }

    $this->otpService->verify(
        $data['phone_number'],
        $data['code']
    );

    $user->update([

        'password' => Hash::make(
            $data['password']
        )

    ]);

    return [

        'message' => 'Password reset successfully.'

    ];
}











}
 