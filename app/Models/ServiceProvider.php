<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceProvider extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'service_category_id',
        'service_area_id',
        'inspection_price',
        'rejection_reason',
        'block_reason',
        'bio',
        'experience_years',
        'rating',
        'latitude',
        'longitude',
        'working_from',
        'working_to',
        'do_not_disturb',
        'availability_status',
        'account_status',
        'blocked_until'
    ];

    protected $casts = [
        'inspection_price' => 'decimal:2',
        'rating'           => 'decimal:2',
        'latitude'         => 'decimal:7',
        'longitude'        => 'decimal:7',
        'working_from'     => 'datetime:H:i',
        'working_to'       => 'datetime:H:i',
        'blocked_until'    => 'datetime',
    ];

    public function profileImage(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable')
            ->where('type', 'profile')
            ->latestOfMany();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function serviceCategory()
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    public function serviceArea()
    {
        return $this->belongsTo(ServiceArea::class);
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function offers()
    {
        return $this->hasMany(Offer::class);
    }

    public function reviews()//**//
    {
        return $this->hasMany(Review::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(SubscriptionProvider::class);
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function blockedServiceProvider()
    {
        return $this->hasMany(BlockedServiceProvider::class);
    }

    public function complaintsAgainst()
    {
        return $this->hasMany(Complaint::class, 'against_user_id', 'user_id');
    }
}
