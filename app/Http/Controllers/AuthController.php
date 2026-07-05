<?php

namespace App\Http\Controllers;
use App\Services\AuthService;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;
use  App\Http\Requests\RegisterRequest;
use  App\Http\Requests\LoginRequest;
class AuthController extends Controller
{
    public function __construct(
    private AuthService $authService
)
{}

public function register(RegisterRequest $request)
{
    $result = $this->authService->register(
        $request->validated()
    );

    return response()->json([
        'message' => 'Registered successfully',
        'user' => new UserResource($result['user']),
        'token' => $result['token'],
    ], 201);
}


public function login(LoginRequest $request){
$result=$this->authService->login($request->validated());
return response()->json(['message'=>'Login successful',
'user'=>new UserResource($result['user']),
 'token' => $result['token']
]);
}
public function logout(Request $request){
$this->authService->logout($request->user());
return response()->json(['message'=>'Logout successful']);
}
}
