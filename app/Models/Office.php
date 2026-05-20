<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Office extends Model
{
    protected $fillable = [
        'municipality_id',
        'name',
        'address',
        'phone',
        'email',
        'image_url',
        'latitude',
        'longitude',
        'working_hours',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'working_hours' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }

    public function staffUsers(): HasMany
    {
        return $this->hasMany(User::class)->where('role', User::ROLE_STAFF);
    }

    public function timeSlots(): HasMany
    {
        return $this->hasMany(OfficeTimeSlot::class);
    }

    public function timeSlotBlocks(): HasMany
    {
        return $this->hasMany(OfficeTimeSlotBlock::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function completedServiceRequests()
    {
        return $this->serviceRequests()->where('status', 'completed');
    }

    public function feedbackFromCompletedRequests()
    {
        return $this->hasManyThrough(
            Feedback::class,
            ServiceRequest::class,
            'office_id',
            'service_request_id',
            'id',
            'id'
        )->where('service_requests.status', 'completed');
    }

    public function scopeWithDiscoveryStats(Builder $query): Builder
    {
        return $query
            ->addSelect([
                'services_count' => ServiceRequest::query()
                    ->selectRaw('count(distinct service_id)')
                    ->whereColumn('service_requests.office_id', 'offices.id')
                    ->where('service_requests.status', 'completed'),
            ])
            ->withCount([
                'feedbackFromCompletedRequests as ratings_count',
            ])
            ->withAvg([
                'feedbackFromCompletedRequests as average_rating',
            ], 'rating');
    }
}
