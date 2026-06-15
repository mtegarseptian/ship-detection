<?php

namespace App\Policies;

use App\Models\Detection;
use App\Models\User;

class DetectionPolicy
{
    public function view(User $user, Detection $detection): bool
    {
        return $user->isAdmin() || $user->id === $detection->user_id;
    }
}