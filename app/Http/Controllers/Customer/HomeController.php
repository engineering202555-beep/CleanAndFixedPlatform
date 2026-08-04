<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Customer\HomeResource;
use App\Services\Home\HomeServiceCustomer;

class HomeController extends Controller
{
    public function __construct(
        private HomeService $homeService
    ) {}

    public function HomeCustomer()
    {
        $home = $this->homeService->HomeCustomer(
            auth()->user()
        );

        return new HomeResource($home);
    }
}