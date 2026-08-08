<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionProvider extends Model
{
    use HasFactory;

    protected $table = 'subscription_providers';

    protected $fillable = [
        'service_provider_id',
        'subscription_id',
        'starts_at',
        'ends_at',
        'status',
        'used_requests',
        'is_complimentary',
        'price_paid',
        'requests_limit',
    ];

    protected function casts(): array
    {
        return [
            'starts_at'        => 'datetime',
            'ends_at'          => 'datetime',
            'is_complimentary' => 'boolean',
            'price_paid'       => 'decimal:2',
        ];
    }

    public function serviceProvider()
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }
}
