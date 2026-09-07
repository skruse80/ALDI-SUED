<?php

namespace App\Policies;

use App\Models\FailedJob;
use App\Models\User;

class FailedJobPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, FailedJob $failedJob): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, FailedJob $failedJob): bool
    {
        return false;
    }

    public function delete(User $user, FailedJob $failedJob): bool
    {
        return true;
    }
}
