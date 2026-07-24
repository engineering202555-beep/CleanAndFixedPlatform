<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'service_area_id',
        'status',
        'blocked_until',
           'counter_urgent_requests_during_day',
        'counter_cancel_by_system',
      
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
