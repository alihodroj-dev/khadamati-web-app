<?php

namespace App\Models;

use App\Support\RequiredDocumentDefinition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ServiceRequest extends Model
{
    protected $fillable = [
        'user_id',
        'service_id',
        'office_id',
        'assigned_staff_id',
        'reference_number',
        'tracking_token',
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

    public function office()
    {
        return $this->belongsTo(Office::class);
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
     * Required document definitions that have not been uploaded yet.
     *
     * @return list<array{
     *     key: string,
     *     label: string,
     *     required: bool,
     *     accepted_types: list<string>,
     *     max_size_mb: int
     * }>
     */
    public function missingRequiredDocuments(): array
    {
        if (! $this->relationLoaded('service') || ! $this->relationLoaded('documents')) {
            return [];
        }

        $definitions = RequiredDocumentDefinition::normalizeList(
            $this->service->required_documents ?? []
        );

        if ($definitions === []) {
            return [];
        }

        $uploadedKeys = $this->documents
            ->map(fn ($document) => RequiredDocumentDefinition::resolveTypeKey(
                (string) $document->document_type,
                $definitions
            ))
            ->filter()
            ->unique()
            ->all();

        return array_values(array_filter(
            $definitions,
            fn (array $definition) => ! in_array($definition['key'], $uploadedKeys, true)
        ));
    }

    public static function generateTrackingToken(): string
    {
        do {
            $token = Str::random(48);
        } while (static::where('tracking_token', $token)->exists());

        return $token;
    }
}
