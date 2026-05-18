<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Support\StaffOfficeScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    const ROLE_CITIZEN = 'citizen';
    const ROLE_STAFF = 'staff';
    const ROLE_ADMIN = 'admin';

    protected $fillable = [
        'name',
        'first_name',
        'last_name',
        'father_name',
        'mother_name',
        'date_of_birth',
        'email',
        'password',
        'role',
        'office_id',
        'phone',
        'national_id',
        'id_document_path',
        'id_front_path',
        'id_back_path',
        'is_active',
        'fcm_token',
        'email_verified_at',
        'push_notifications_enabled',
        'email_notifications_enabled',
        'sms_notifications_enabled',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'date_of_birth' => 'date',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'push_notifications_enabled' => 'boolean',
            'email_notifications_enabled' => 'boolean',
            'sms_notifications_enabled' => 'boolean',
        ];
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    // Service Requests
    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function assignedRequests(): HasMany
    {
        return $this->hasMany(
            ServiceRequest::class,
            'assigned_staff_id'
        );
    }

    // Documents
    public function uploadedDocuments(): HasMany
    {
        return $this->hasMany(
            RequestDocument::class,
            'uploaded_by'
        );
    }

    // Appointments
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function staffAppointments(): HasMany
    {
        return $this->hasMany(
            Appointment::class,
            'staff_id'
        );
    }

    // Payments
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // Feedback
    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    public function deviceTokens(): HasMany
    {
        return $this->hasMany(DeviceToken::class);
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    // Role Checks
    public function isCitizen(): bool
    {
        return $this->role === self::ROLE_CITIZEN;
    }

    public function isStaff(): bool
    {
        return $this->role === self::ROLE_STAFF;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function managesOffice(?int $officeId): bool
    {
        return StaffOfficeScope::canAccessOffice($this, $officeId);
    }
}
