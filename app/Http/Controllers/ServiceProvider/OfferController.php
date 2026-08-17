<?php

namespace App\Http\Controllers\ServiceProvider;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceProvider\ServiceRequest\StoreOfferRequest;
use App\Http\Resources\ServiceProvider\OfferResource;
use App\Models\Offer;
use App\Models\ServiceRequest;
use App\Services\Offer\OfferService;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    public function __construct(
        private readonly OfferService $service
    ) {
    }

    public function store(StoreOfferRequest $request, ServiceRequest $serviceRequest)
    {
        $provider = $request->user()->serviceProvider;

        $offer = $this->service->createOffer($provider, $serviceRequest, $request->validated());

        return ApiResponse::success(OfferResource::make($offer), 'تم إرسال العرض بنجاح', 201);
    }

    public function index(Request $request)
    {
        $provider = $request->user()->serviceProvider;

        $offers = Offer::query()
            ->where('service_provider_id', $provider->id)
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate($request->integer('per_page', 15));

        $paginated = OfferResource::collection($offers)->response()->getData(true);

        return ApiResponse::success($paginated, 'تم جلب عروضك بنجاح');
    }
}

