<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialAccount extends Model
{
    public const PROVIDER_GOOGLE = 'google';

    public const PROVIDER_APPLE = 'apple';

    protected $fillable = [
        'user_id',
        'provider',
        'provider_user_id',
        'email',
        'avatar_url',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
