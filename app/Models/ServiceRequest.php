<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    protected $fillable = [
        'user_id',
        'service_id',
        'assigned_staff_id',
        'reference_number',
        'status',
        'citizen_notes',
        'staff_notes',
        'rejection_reason',
        'submitted_data',
        'submitted_at',
        'reviewed_at',
        'completed_at',
    ];

    protected $casts = [
        'submitted_data' => 'array',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function assignedStaff()
    {
        return $this->belongsTo(User::class, 'assigned_staff_id');
    }

    public function documents()
    {
        return $this->hasMany(RequestDocument::class);
    }

    public function appointment()
    {
        return $this->hasOne(Appointment::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    public function feedback()
    {
        return $this->hasOne(Feedback::class);
    }

    /**
     * Required document types that have not been uploaded yet.
     *
     * @return list<string>
     */
    public function missingDocumentTypes(): array
    {
        if (! $this->relationLoaded('service') || ! $this->relationLoaded('documents')) {
            return [];
        }

        $required = $this->service->required_documents ?? [];

        if ($required === []) {
            return [];
        }

        $uploadedTypes = $this->documents
            ->pluck('document_type')
            ->unique()
            ->all();

        return array_values(array_diff($required, $uploadedTypes));
    }
}
