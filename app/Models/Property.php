<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Property extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'region_id',
        'property_code',
        'type',
        'title',
        'address',
        'suburb',
        'city',
        'province',
        'bedrooms',
        'size_sqm',
        'monthly_rent',
        'payment_frequency',
        'status',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'monthly_rent' => 'decimal:2',
            'size_sqm' => 'decimal:2',
            'bedrooms' => 'integer',
        ];
    }

    /**
     * Get the region this property belongs to.
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Get the tenancies for this property.
     */
    public function tenancies(): HasMany
    {
        return $this->hasMany(Tenancy::class);
    }

    /**
     * Get the activity log options for this model.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'region_id', 'property_code', 'type', 'title', 'address',
                'suburb', 'city', 'province', 'bedrooms', 'size_sqm',
                'monthly_rent', 'payment_frequency', 'status', 'notes',
            ]);
    }
}
