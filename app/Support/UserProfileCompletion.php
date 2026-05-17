<?php

namespace App\Support;

use App\Models\User;

class UserProfileCompletion
{
    public static function isCompleted(User $user): bool
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
}
