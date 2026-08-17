<?php

namespace App\Http\Controllers\ServiceProvider;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceProvider\ServiceRequest\StoreFcmTokenRequest;
use App\Models\FcmToken;

class FcmTokenController extends Controller
{
    /**
     * updateOrCreate بمفتاح fcm_token نفسه (مش user_id) — لأنه
     * fcm_token عمود unique بالجدول، وهذا بيغطي سيناريو واقعي: نفس
     * الجهاز اتسجل فيه حساب مختلف بعدين (Logout/Login)، بيصير
     * user_id ينتقل لصاحب الجهاز الجديد بدل ما تصير Duplicate Key
     * Exception.
     */
    public function store(StoreFcmTokenRequest $request)
    {
        FcmToken::updateOrCreate(
            ['fcm_token' => $request->validated('fcm_token')],
            ['user_id' => $request->user()->id]
        );

        return ApiResponse::success(null, 'تم حفظ التوكن بنجاح');
    }
}
