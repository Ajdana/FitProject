<?php

namespace App\Repositories;

use App\Models\ScanHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ScanHistoryRepository
{
    public function baseQuery(): Builder
    {
        return ScanHistory::with('users')->latest();
    }

    public function applyDateFilter(Builder $query, ?string $from, ?string $to): Builder
    {
        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query;
    }

    public function forAdmin(?string $from, ?string $to)
    {
        $query = $this->baseQuery();
        $this->applyDateFilter($query, $from, $to);

        return $query->paginate(10);
    }

    public function forUser(int $userId, ?string $from, ?string $to)
    {
        $query = $this->baseQuery();

        $query->whereHas('users', function ($q) use ($userId) {
            $q->where('users.id', $userId);
        });

        $this->applyDateFilter($query, $from, $to);

        return $query->get();
    }


    public function delete(ScanHistory $scan): void
    {
        $scan->delete();
    }
}
