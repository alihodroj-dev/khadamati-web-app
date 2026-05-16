<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdentityVerificationSession extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_VERIFIED = 'verified';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'session_token',
        'id_front_path',
        'id_back_path',
        'ocr_raw_text',
        'extracted_data',
        'status',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'extracted_data' => 'array',
            'expires_at' => 'datetime',
        ];
    }
}
