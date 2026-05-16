<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RequestDocument extends Model
{
    public const SOURCE_CITIZEN = 'citizen';

    public const SOURCE_STAFF = 'staff';

    public const SOURCE_SYSTEM = 'system';

    public const PURPOSE_REQUIREMENT = 'requirement';

    public const PURPOSE_OFFICIAL_RESPONSE = 'official_response';

    public const PURPOSE_CERTIFICATE = 'certificate';

    public const PURPOSE_RECEIPT = 'receipt';

    public const PURPOSE_OTHER = 'other';

    protected $fillable = [
        'service_request_id',
        'uploaded_by',
        'source',
        'purpose',
        'document_type',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'status',
        'rejection_reason',
    ];

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function isCitizenRequirement(): bool
    {
        return $this->source === self::SOURCE_CITIZEN
            && $this->purpose === self::PURPOSE_REQUIREMENT;
    }

    public function isOfficialOutput(): bool
    {
        return in_array($this->purpose, [
            self::PURPOSE_OFFICIAL_RESPONSE,
            self::PURPOSE_CERTIFICATE,
            self::PURPOSE_RECEIPT,
            self::PURPOSE_OTHER,
        ], true);
    }

    public function scopeRequirement(Builder $query): Builder
    {
        return $query->where('purpose', self::PURPOSE_REQUIREMENT);
    }

    public function scopeOfficial(Builder $query): Builder
    {
        return $query->whereIn('purpose', [
            self::PURPOSE_OFFICIAL_RESPONSE,
            self::PURPOSE_CERTIFICATE,
            self::PURPOSE_RECEIPT,
            self::PURPOSE_OTHER,
        ]);
    }
}
