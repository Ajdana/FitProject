<?php

namespace App\Policies;

use App\Models\ScanHistory;
use App\Models\User;


class ScanHistoryPolicy
{
    public function view(User $user, ScanHistory $scan)
    {
        // admin может всё
        if ($user->can('scan.read.all')) {
            return true;
        }

        // user может только свои
        return $scan->users()
            ->where('users.id', $user->id)
            ->exists();
    }

    public function delete(User $user, ScanHistory $scan)
    {
        return $user->can('scan.delete.all');
    }
}
