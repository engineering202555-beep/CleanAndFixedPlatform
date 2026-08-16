<?php

namespace App\Services\ServiceProvider;

use App\Models\Complaint;
use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Collection;
class StoreProviderComplaintService
{
   public function storeProviderComplaint(
        
    User $user,
    array $data
): Complaint {

    return DB::transaction(function () use ($user, $data) {

        /*
        ============================================
        1- جلب Service Provider
        ============================================
        */

        $provider = ServiceProvider::where(
            'user_id',
            $user->id
        )->firstOrFail();


        /*
        ============================================
        2- جلب Service Request
        ============================================
        */

        $serviceRequest = ServiceRequest::findOrFail(
            $data['service_request_id']
        );


        /*
        ============================================
        3- البحث عن Offer الخاصة بهذا Provider
        ============================================
        */

        $providerOffer = $serviceRequest->offers()
            ->where(
                'service_provider_id',
                $provider->id
            )
            ->with('serviceRequest.customer.user')
            ->first();


        /*
        ============================================
        4- التأكد أن هذا Provider مرتبط بالطلب
        ============================================
        */

        if (!$providerOffer) {

            throw ValidationException::withMessages([
                'service_request_id' => [
                    'This service request is not related to you.'
                ]
            ]);
        }


        /*
        ============================================
        5- جلب Customer صاحب الطلب
        ============================================
        */

        $customer = $serviceRequest->customer;


        if (!$customer) {

            throw ValidationException::withMessages([
                'service_request_id' => [
                    'This request has no customer.'
                ]
            ]);
        }


        /*
        ============================================
        6- تحديد User الخاص بالزبون تلقائيًا
        ============================================
        */

        $againstUserId = $customer->user_id;


        /*
        ============================================
        7- إنشاء الشكوى
        ============================================
        */

        $complaint = Complaint::create([

            // مقدم الخدمة صاحب الشكوى
            'user_id' => $user->id,

            // الزبون الذي الشكوى ضده
            'against_user_id' => $againstUserId,

            // الطلب المرتبط
            'service_request_id' => $serviceRequest->id,

            'reason' => $data['reason'],

            'description' => $data['description'],

            'status' => 'pending',

            'admin_notes' => null,
        ]);


        /*
        ============================================
        8- إرجاع الشكوى مع العلاقات
        ============================================
        */

        return $complaint->load([
            'user',
            'againstUser',
            'serviceRequest',
        ]);
    });
}

    public function getProviderComplaints(User $user): Collection
    {
        return Complaint::query()
            ->where('user_id', $user->id)
            ->with([
                'againstUser',
                'serviceRequest',
            ])
            ->latest()
            ->get();
    }

    
public function getComplaintsAgainstProvider(User $user): Collection
{
    \Log::info('Provider complaints check', [
        'logged_user_id' => $user->id,
        'complaints_against_me' => Complaint::where(
            'against_user_id',
            $user->id
        )->get()->toArray(),
    ]);

    return Complaint::query()
        ->where('against_user_id', $user->id)
        ->with([
            'user',
            'serviceRequest',
        ])
        ->latest()
        ->get();
}














}