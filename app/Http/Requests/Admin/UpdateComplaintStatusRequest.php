<?php

namespace App\Http\Requests\Admin;

use App\Models\Complaint;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateComplaintStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Complaint $complaint */
        $complaint = $this->route('complaint');
        $allowedNext = Complaint::allowedNextStatuses($complaint->status);

        return [
            // القيم المسموحة بـ status محسوبة ديناميكياً حسب الحالة
            // الحالية للشكوى بالذات — مش قائمة ثابتة، فمستحيل تنتقلي
            // بضغطة واحدة لحالة غير منطقية.
            'status' => ['required', Rule::in($allowedNext)],

            // إجباري بس لو القرار نهائي (resolved/rejected)، حسب
            // القيمة المُرسلة فعلياً بـ status.
            'admin_notes' => [
                Rule::requiredIf(in_array($this->input('status'), Complaint::STATUSES_REQUIRING_NOTES, true)),
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * لو الشكوى أصلاً بحالة نهائية (resolved/rejected)، allowedNext
     * بترجع مصفوفة فاضية، وRule::in([]) بترفض أي قيمة برسالة عامة
     * غامضة ("القيمة المختارة غير صالحة"). هون منوضح السبب الحقيقي.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var Complaint $complaint */
            $complaint = $this->route('complaint');

            if (empty(Complaint::allowedNextStatuses($complaint->status))) {
                $validator->errors()->add(
                    'status',
                    "هذه الشكوى بحالة نهائية ({$complaint->status})، لا يمكن تغيير حالتها بعد الآن."
                );
            }
        });
    }
}
