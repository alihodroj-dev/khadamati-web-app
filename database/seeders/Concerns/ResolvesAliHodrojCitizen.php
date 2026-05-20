<?php

namespace Database\Seeders\Concerns;

use App\Models\ServiceRequest;
use App\Models\User;

trait ResolvesAliHodrojCitizen
{
    protected const ALI_EMAIL = 'hodroj.ali.2004@gmail.com';

    protected function aliCitizen(): ?User
    {
        return User::query()->where('email', self::ALI_EMAIL)->first();
    }

    protected function aliServiceRequest(string $referenceSuffix): ?ServiceRequest
    {
        return ServiceRequest::query()
            ->where('reference_number', 'like', '%'.$referenceSuffix)
            ->whereHas('user', fn ($q) => $q->where('email', self::ALI_EMAIL))
            ->first();
    }
}
