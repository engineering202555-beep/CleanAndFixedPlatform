<?php

namespace App\Services\Complaint;

use App\Models\Complaint;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class ComplaintStatusService
{
    /**
     * دفاع مزدوج (Defense in Depth): الـ Request أصلاً بيمنع القيم
     * غير المنطقية بـ 422، وهاد الفحص هون طبقة حماية ثانية بمستوى
     * الـ Service — لو نودي الدالة يوماً من مكان تاني (Console
     * Command، Job) بدون المرور عبر UpdateComplaintStatusRequest،
     * برضو ما رح توصل لحالة غير منطقية.
     *
     * لاحظي: التحديث هون بيلمس status وadmin_notes بس — ولا عمود
     * تاني (user_id, against_user_id, service_request_id, reason,
     * description) قابل للمس من هالمسار إطلاقاً، حتى لو حداً
     * حاول يمررهم بالغلط بالـ array.
     */
    public function transition(Complaint $complaint, array $data): Complaint
    {
        $allowed = Complaint::allowedNextStatuses($complaint->status);

        if (! in_array($data['status'], $allowed, true)) {
            throw new ConflictHttpException(
                "لا يمكن تحويل الشكوى من الحالة '{$complaint->status}' إلى '{$data['status']}'."
            );
        }

        $complaint->update([
            'status'      => $data['status'],
            'admin_notes' => $data['admin_notes'] ?? $complaint->admin_notes,
        ]);

        return $complaint;
    }
}
