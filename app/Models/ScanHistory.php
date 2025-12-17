<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ScanHistory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'image',
        'result'
    ];

    protected $casts = [
        'result' => 'array',
    ];

    public function users()
    {
        return $this->belongsToMany(
            \App\Models\User::class,
            'scan_history_user',
            'scan_history_id',
            'user_id'
        );
    }
}
