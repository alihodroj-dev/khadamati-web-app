<?php

namespace App\Support;

use App\Models\User;
use App\Support\ServiceRequestStatus;
use Illuminate\Database\Eloquent\Builder;

class StaffOfficeScope
{
    public static function appliesTo(User $user): bool
    {
        return $user->isStaff() && $user->office_id !== null;
    }

    public static function canAccessOffice(User $user, ?int $officeId): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if (! self::appliesTo($user) || $officeId === null) {
            return false;
        }

        return (int) $user->office_id === (int) $officeId;
    }

    public static function applyServiceRequestScope(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        if (self::appliesTo($user)) {
            return $query->where('office_id', $user->office_id);
        }

        if ($user->isStaff()) {
            return $query->whereRaw('1 = 0');
        }

        return $query;
    }

    public static function applyAppointmentScope(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        if (self::appliesTo($user)) {
            return $query->whereHas('serviceRequest', function (Builder $serviceRequestQuery) use ($user) {
                $serviceRequestQuery->where('office_id', $user->office_id);
            });
        }

        if ($user->isStaff()) {
            return $query->whereRaw('1 = 0');
        }

        return $query;
    }

    public static function applyPaymentScope(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        if (self::appliesTo($user)) {
            return $query->whereHas('serviceRequest', function (Builder $serviceRequestQuery) use ($user) {
                $serviceRequestQuery->where('office_id', $user->office_id);
            });
        }

        if ($user->isStaff()) {
            return $query->whereRaw('1 = 0');
        }

        return $query;
    }

    public static function applyFeedbackScope(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        if (self::appliesTo($user)) {
            return $query->whereHas('serviceRequest', function (Builder $serviceRequestQuery) use ($user) {
                $serviceRequestQuery
                    ->where('office_id', $user->office_id)
                    ->where('status', ServiceRequestStatus::COMPLETED);
            });
        }

        if ($user->isStaff()) {
            return $query->whereRaw('1 = 0');
        }

        return $query;
    }

    public static function applyServiceScope(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        if (self::appliesTo($user)) {
            return $query->where(function (Builder $serviceQuery) use ($user) {
                $serviceQuery
                    ->where('office_id', $user->office_id)
                    ->orWhereNull('office_id');
            });
        }

        if ($user->isStaff()) {
            return $query->whereRaw('1 = 0');
        }

        return $query;
    }
}
