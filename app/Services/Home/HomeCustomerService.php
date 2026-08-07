<?php

namespace App\Services\Home;

use App\Models\Customer;
use App\Models\Notification;
use App\Models\ServiceCategory;
use App\Models\ServiceRequest;
use App\Models\User;

class HomeCustomerService
{
    public function HomeCustomer(User $user): array
    {
        /*
        ============================================
        1- جلب الزبون
        ============================================
        */

        $customer = Customer::with([
            'user',
            'serviceArea'
        ])
        ->where('user_id', $user->id)
        ->firstOrFail();

        /*
        ============================================
        2- عدد الإشعارات غير المقروءة
        ============================================
        */

        $notificationsCount = Notification::where(
            'user_id',
            $user->id
        )
        ->where('is_read', false)
        ->count();

        /*
        ============================================
        3- التصنيفات الشائعة
        ============================================
        */

        $popularCategories = ServiceCategory::take(4)->get();

        /*
        ============================================
        4- خصم أول طلب
        ============================================
        */

       $firstOrderOffer = !$customer->first_order_discount_used;

        return [

            'customer' => $customer,

            'notifications_count' => $notificationsCount,

            'popular_categories' => $popularCategories,

            'first_order_offer' => $firstOrderOffer,

        ];
    }
}