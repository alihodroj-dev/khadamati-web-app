<?php

namespace App\Support;

class ServiceRequestStatus
{
    public const PENDING = 'pending';

    public const UNDER_REVIEW = 'under_review';

    public const REQUIRES_ACTION = 'requires_action';

    public const APPROVED = 'approved';

    public const REJECTED = 'rejected';

    public const COMPLETED = 'completed';

    public const CANCELLED = 'cancelled';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::PENDING,
            self::UNDER_REVIEW,
            self::REQUIRES_ACTION,
            self::APPROVED,
            self::REJECTED,
            self::COMPLETED,
            self::CANCELLED,
        ];
    }

    /**
     * @return list<string>
     */
    public static function staffUpdatable(): array
    {
        return [
            self::UNDER_REVIEW,
            self::REQUIRES_ACTION,
            self::APPROVED,
            self::REJECTED,
            self::COMPLETED,
        ];
    }

    /**
     * @return list<string>
     */
    public static function adminUpdatable(): array
    {
        return self::all();
    }

    /**
     * @return list<string>
     */
    public static function cancellable(): array
    {
        return [
            self::PENDING,
            self::UNDER_REVIEW,
            self::REQUIRES_ACTION,
        ];
    }

    /**
     * @param  list<string>  $statuses
     */
    public static function validationRule(array $statuses): string
    {
        return 'in:'.implode(',', $statuses);
    }
}
