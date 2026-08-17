<?php

namespace App\Http\Controllers\ServiceProvider;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceProvider\ServiceRequest\UpdateDoNotDisturbRequest;
use App\Services\ServiceRequest\ProviderPreferencesService;

class ProviderPreferencesController extends Controller
{
    public function __construct(
        private readonly ProviderPreferencesService $service
    ) {
    }

    public function updateDoNotDisturb(UpdateDoNotDisturbRequest $request)
    {
        $provider = $request->user()->serviceProvider;

        $updated = $this->service->updateDoNotDisturb($provider, $request->boolean('do_not_disturb'));

        $message = $updated->do_not_disturb
            ? 'تم تفعيل عدم الإزعاج بنجاح'
            : 'تم إلغاء تفعيل عدم الإزعاج بنجاح';

        return ApiResponse::success(['do_not_disturb' => $updated->do_not_disturb], $message);
    }
}
