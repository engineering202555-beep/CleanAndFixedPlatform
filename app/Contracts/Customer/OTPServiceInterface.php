<?php

namespace App\Contracts\Customer;

use App\Models\User;

interface OTPServiceInterface
{
    public function generate(User $user): void;

   public function verify(string $phone, string $code): bool;

    public function resend(User $user): void;
}