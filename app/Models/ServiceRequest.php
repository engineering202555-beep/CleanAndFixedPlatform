<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'service_provider_id',
        'service_category_id',
        'service_area_id',
        'description',
        'status',
        'starts_at',
        'ends_at',
        'latitude_x',
        'longitude_y',
        'is_urgent',
        'search_level',
        'expires_at',
        'duration_in_minutes'
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_urgent' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    public function serviceCategory()
    {
        return $this->belongsTo(ServiceCategory::class);
    }

    public function serviceArea()
    {
        return $this->belongsTo(ServiceArea::class);
    }

    public function offers()
    {
        return $this->hasMany(Offer::class);
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    public function complaint()
    {
        return $this->hasOne(Complaint::class);
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }
}
