<?php
namespace App\Services\Customer;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class CustomerManageService
{
    /**
     * الحالات "النهائية" فقط يُسمح حذف الزبون معها. أي طلب لسا
     * بأي مرحلة من مراحل البحث/التنفيذ (بما فيها الكشف) يمنع الحذف.
     */
    private const FINISHED_STATUSES = [
        'completed',
        'rejected',
        'fault_detected',
        'cancel_by_customer',
        'cancel_by_provider',
        'cancel_by_system',
    ];

    /**
     * حذف ناعم (Soft Delete) للزبون وحساب المستخدم المرتبط فيه معاً،
     * بشرط ما يكون عنده أي طلب لسا نشط (لم يصل لحالة نهائية).
     */
    public function delete(Customer $customer): void
    {
        $this->ensureDeletable($customer);

        DB::transaction(function () use ($customer) {
            $customer->delete();
            $customer->user->delete();
        });
    }

    public function block(Customer $customer, array $data): void
    {
        $this->ensureBlockable($customer);

        $customer->update([
            'status' => 'blocked',
            'block_reason' => $data['reason'] ?? $customer->block_reason,
            'blocked_until' => now()->addDays((int)$data['duration_in_days']),
        ]);
    }

    public function unblock(Customer $customer): void
    {
        if ($customer->status !== 'blocked') {
            throw new ConflictHttpException('الزبون غير محظور حالياً.');
        }

        $customer->update([
            'status' => 'active',
            'block_reason' => null,
            'blocked_until' => null,
        ]);
    }

    private function ensureDeletable(Customer $customer): void
    {
        if ($this->hasUnfinishedRequests($customer)) {
            throw new ConflictHttpException(
                'لا يمكن حذف زبون لديه طلب خدمة لم يصل لحالة نهائية بعد.'
            );
        }
    }

    private function hasUnfinishedRequests(Customer $customer): bool
    {
        return $customer->serviceRequests()
            ->whereNotIn('status', self::FINISHED_STATUSES)
            ->exists();
    }

    private function ensureBlockable(Customer $customer): void
    {
        if (! in_array($customer->status, ['active'], true)) {
            throw new ConflictHttpException(
                'لا يمكن حظر زبون محظور مسبقاً'
            );
        }

        if ($this->hasUnfinishedRequests($customer)) {
            throw new ConflictHttpException(
                'لا يمكن حظر زبون لديه طلبات مقبولة لم تكتمل بعد.'
            );
        }
    }
}
