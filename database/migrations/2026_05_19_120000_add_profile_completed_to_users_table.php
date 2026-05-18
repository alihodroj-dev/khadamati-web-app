<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('profile_completed')->default(false)->after('is_active');
        });

        User::query()->each(function (User $user): void {
            $completed = filled($user->first_name)
                && filled($user->last_name)
                && filled($user->father_name)
                && filled($user->mother_name)
                && filled($user->date_of_birth)
                && filled($user->national_id)
                && filled($user->id_front_path)
                && filled($user->id_back_path);

            if ($completed) {
                $user->forceFill(['profile_completed' => true])->save();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('profile_completed');
        });
    }
};
