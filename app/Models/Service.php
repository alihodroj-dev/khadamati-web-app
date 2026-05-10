<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'service_category_id',
        'name',
        'description',
        'base_fee',
        'estimated_processing_days',
        'required_documents',
        'requires_appointment',
        'is_active',
    ];

    protected $casts = [
        'required_documents' => 'array',
        'base_fee' => 'decimal:2',
        'requires_appointment' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }
}
