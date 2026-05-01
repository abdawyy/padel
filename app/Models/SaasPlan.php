<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaasPlan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'monthly_price',
        'yearly_price',
        'features',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'monthly_price' => 'decimal:2',
            'yearly_price'  => 'decimal:2',
            'features'      => 'array',
            'is_active'     => 'boolean',
            'sort_order'    => 'integer',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(ClubSaasSubscription::class);
    }

    public function activeSubscriptions(): HasMany
    {
        return $this->hasMany(ClubSaasSubscription::class)->where('status', 'active');
    }

    public function priceFor(string $cycle): float
    {
        return (float) ($cycle === 'yearly' ? $this->yearly_price : $this->monthly_price);
    }

    public function getFeatureLabel(string $key, mixed $default = null): mixed
    {
        return $this->features[$key] ?? $default;
    }
}
