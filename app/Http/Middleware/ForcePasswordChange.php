<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChange
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $admin = auth()->user();

        if (
            $admin->must_change_password &&
            !$request->is('api/admin/change-password')
        ) {
            return ApiResponse::error(
                'You must change your password first.',
                403
            );
        }

        return $next($request);
    }
}
