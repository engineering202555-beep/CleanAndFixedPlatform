<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'against_user_id',
        'service_request_id',
        'reason',
        'description',
        'status',
        'admin_notes',
    ];

    /**
     * مركزية واحدة لكل الانتقالات المسموحة — الـ Request والـ Service
     * كلاهما بيرجعوا لنفس المصدر، مش نسختين مختلفتين من نفس المنطق.
     */
    public const STATUS_TRANSITIONS = [
        'pending'   => ['in_review'],
        'in_review' => ['resolved', 'rejected'],
        'resolved'  => [],
        'rejected'  => [],
    ];

    /**
     * الحالات يلي admin_notes فيها إجباري — قرار نهائي لازم يترافق
     * مع تبرير موثّق دايماً.
     */
    public const STATUSES_REQUIRING_NOTES = ['resolved', 'rejected'];

    public static function allowedNextStatuses(string $currentStatus): array
    {
        return self::STATUS_TRANSITIONS[$currentStatus] ?? [];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function againstUser()
    {
        return $this->belongsTo(User::class, 'against_user_id');
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

}
