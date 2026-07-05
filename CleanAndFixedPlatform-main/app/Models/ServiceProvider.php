<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'service_category_id',
        'service_area_id',
        'bio',
        'experience_years',
        'is_approved',
        'rating',
        'working_from',
        'working_to',
        'availability_status',
        'account_status',
    ];

    protected function casts(): array
    {
        return [
            'is_approved' => 'boolean',
            'working_from' => 'datetime:H:i',
            'working_to' => 'datetime:H:i',
        ];
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
}
