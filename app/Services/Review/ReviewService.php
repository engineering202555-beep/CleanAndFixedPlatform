<?php

namespace App\Services\Review;

use App\Models\Customer;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReviewService
{
    public function Review(User $user, array $data): array
    {
        return DB::transaction(function () use ($user, $data) {

            /*
            ==========================================
            1- جلب الزبون
            ==========================================
            */

            $customer = Customer::where(
                'user_id',
                $user->id
            )->firstOrFail();

            /*
            ==========================================
            2- جلب الطلب
            ==========================================
            */

            $serviceRequest = $customer
                ->serviceRequests()
                ->findOrFail($data['service_request_id']);

            /*
            ==========================================
            3- يجب أن تكون الخدمة منتهية
            ==========================================
            */

            if ($serviceRequest->status != 'completed') {

                throw ValidationException::withMessages([
                    'review' => [
                        'You can review only completed requests.'
                    ]
                ]);
            }

            /*
            ==========================================
            4- التأكد أنه لم يقيم سابقاً
            ==========================================
            */

            if (
                Review::where(
                    'service_request_id',
                    $serviceRequest->id
                )->exists()
            ) {

                throw ValidationException::withMessages([
                    'review' => [
                        'You already reviewed this request.'
                    ]
                ]);
            }

            /*
            ==========================================
            5- معرفة مقدم الخدمة
            ==========================================
            */

            $acceptedOffer = $serviceRequest
                ->offers()
                ->where('status', 'accepted')
                ->first();

            if (!$acceptedOffer) {

                throw ValidationException::withMessages([
                    'offer' => [
                        'Accepted offer not found.'
                    ]
                ]);
            }

            /*
            ==========================================
            6- إنشاء التقييم
            ==========================================
            */

            $review = Review::create([

                'customer_id' => $customer->id,

                'service_request_id' => $serviceRequest->id,

                'service_provider_id' => $acceptedOffer->service_provider_id,

                'provider_rating' => $data['provider_rating'],

                'comment' => $data['comment'] ?? null,

            ]);

            /*
            ==========================================
            7- تحديث متوسط التقييم
            ==========================================
            */

            $provider = $acceptedOffer->serviceProvider;

            $provider->update([

                'rating' => Review::where(
                    'service_provider_id',
                    $provider->id
                )->avg('provider_rating')

            ]);

            return [

                'message' => 'Review added successfully.',

                'review_id' => $review->id,

            ];

        });
    }
}