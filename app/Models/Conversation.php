<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_request_id',
        'citizen_id',
        'staff_id',
        'status',
        'last_message_at'
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the service request this conversation belongs to
     */
    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

     /**
     * Get the citizen (user) who owns this conversation
     */
    public function citizen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'citizen_id');
    }

     /**
     * Get the staff member assigned to this conversation
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    /**
     * Get all messages in this conversation
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    /**
     * Get the last message sent in this conversation
     */
    public function lastMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latest('created_at');
    }

    /**
     * Get unread messages count for a specific user
     */
    public function unreadCountForUser(int $userId): int
    {
        return $this->messages()
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Scope for active conversations only
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for conversations assigned to specific staff
     */
    public function scopeForStaff($query, int $staffId)
    {
        return $query->where('staff_id', $staffId);
    }

    /**
     * Scope for conversations belonging to specific citizen
     */
    public function scopeForCitizen($query, int $citizenId)
    {
        return $query->where('citizen_id', $citizenId);
    }
    
}
