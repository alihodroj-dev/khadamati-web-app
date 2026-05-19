<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{

    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'receiver_id',
        'sender_type',
        'message',
        'is_read',
        'read_at'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the conversation this message belongs to
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Get the sender user
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the receiver user
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

     /**
     * Check if message was sent by citizen
     */
    public function isFromCitizen(): bool
    {
        return $this->sender_type === 'citizen';
    }

    /**
     * Check if message was sent by staff
     */
    public function isFromStaff(): bool
    {
        return $this->sender_type === 'staff';
    }

    /**
     * Mark message as read
     */
    public function markAsRead(): void
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now()
            ]);
        }
    }

    /**
     * Scope for unread messages only
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope for messages sent to specific user
     */
    public function scopeReceivedBy($query, int $userId)
    {
        return $query->where('receiver_id', $userId);
    }

     /**
     * Scope for messages sent by specific user
     */
    public function scopeSentBy($query, int $userId)
    {
        return $query->where('sender_id', $userId);
    }

    /**
     * Get formatted message preview (truncated)
     */
    public function getPreviewAttribute(): string
    {
        return strlen($this->message) > 50 
            ? substr($this->message, 0, 50) . '...' 
            : $this->message;
    }

    /**
     * Get time ago in human readable format
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }
}
