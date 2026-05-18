<?php

namespace App\Support;

use App\Models\User;

class UserProfileCompletion
{
    public static function meetsRequirements(User $user): bool
    {
        return filled($user->first_name)
            && filled($user->last_name)
            && filled($user->father_name)
            && filled($user->mother_name)
            && filled($user->date_of_birth)
            && filled($user->national_id)
            && filled($user->id_front_path)
            && filled($user->id_back_path);
    }

    public static function isCompleted(User $user): bool
    {
        return self::meetsRequirements($user);
    }

    public static function sync(User $user): User
    {
        $completed = self::meetsRequirements($user);

        if ((bool) $user->profile_completed !== $completed) {
            $user->forceFill(['profile_completed' => $completed]);
            $user->save();
        }

        return $user->fresh();
    }
}
