<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'service_area_id',
        'status',
        'blocked_until',
        'block_reason',
        'counter_urgent_requests_during_day',
        'counter_cancel_by_system',
        'first_order_discount_used'
    ];

    protected $casts = [
        'blocked_until' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function serviceArea()
    {
        return $this->belongsTo(ServiceArea::class);
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function blockedServiceProvider()
    {
        return $this->hasMany(BlockedServiceProvider::class);
    }
}
