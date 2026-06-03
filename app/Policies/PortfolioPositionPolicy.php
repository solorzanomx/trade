<?php

namespace App\Policies;

use App\Models\PortfolioPosition;
use App\Models\User;

class PortfolioPositionPolicy
{
    public function update(User $user, PortfolioPosition $position): bool
    {
        return $user->id === $position->user_id;
    }

    public function delete(User $user, PortfolioPosition $position): bool
    {
        return $user->id === $position->user_id;
    }
}
