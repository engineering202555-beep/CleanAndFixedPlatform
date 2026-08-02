<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            /*
            ========================================
            بيانات الزبون
            ========================================
            */

            'customer' => [

                'name' => $this['customer']->user->first_name,

                'service_area' => $this['customer']->serviceArea->area_name,

            ],

            /*
            ========================================
            عدد الإشعارات
            ========================================
            */

            'notifications' => [

                'unread_count' => $this['notifications_count']

            ],

            /*
            ========================================
            التصنيفات الشائعة
            ========================================
            */

            'popular_categories' => CategoryResource::collection(

                $this['popular_categories']

            ),

            /*
            ========================================
            خصم أول طلب
            ========================================
            */

            'first_order_offer' =>

            $this['first_order_offer']

            ? [

                'title' => 'Welcome',

                'description' => 'Get 20% off on your first service request.',

                'discount' => '20%'

            ]

            : null,

        ];
    }
}