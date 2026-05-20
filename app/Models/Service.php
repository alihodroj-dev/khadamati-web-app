<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'service_category_id',
        'office_id',
        'name',
        'description',
        'image_url',
        'base_fee',
        'estimated_processing_days',
        'required_documents',
        'requires_appointment',
        'issues_certificate',
        'is_active',
    ];

    protected $casts = [
        'required_documents' => 'array',
        'base_fee' => 'decimal:2',
        'requires_appointment' => 'boolean',
        'issues_certificate' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class);
    }
}
