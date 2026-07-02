<?php
namespace App\Services;

use App\Models\User;
use Illuminate\Validation\ValidationException;
use App\Events\UserRegistered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function register(array $data): array
    {
      return DB::transaction(function () use ($data) {

    $user = User::create([

                'firstName' => $data['firstName'],

                'lastName' => $data['lastName'],

                'email' => $data['email'],

                'phone' => $data['phone'],

                'password' => Hash::make($data['password'])

            ]);

    $user->assignRole('customer');

    $token = $user
        ->createToken('mobile')
        ->plainTextToken;

    event(new UserRegistered($user));

    return [
        'user' => $user,
        'token' => $token,
    ];
});
    }


    public function login(array $data):array
    {

      $user=User::where('email',$data['email'])->first();
      if( !$user){
throw ValidationException::withMessages(['email'=>['البريد الالكتروني او كلمة المرور غير صجيجة'] ]);
      }
if(!Hash::check($data['password'],$user->password)){
    throw ValidationException::withMessages(['email'=>['البريد الالكتروني او كلمة المرور غير صجيجة'] ]);
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
}
 