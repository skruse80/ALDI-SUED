<?php

namespace App\Policies;

use App\Models\PriceHistory;
use App\Models\User;

class PriceHistoryPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, PriceHistory $priceHistory): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, PriceHistory $priceHistory): bool
    {
        return false;
    }

    public function delete(User $user, PriceHistory $priceHistory): bool
    {
        return false;
    }
}
