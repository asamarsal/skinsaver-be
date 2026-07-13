<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Routine extends Model
{
    use HasUuids;

    protected $fillable = [
        'audit_id',
        'morning_steps',
        'night_steps'
    ];

    protected $casts = [
        'morning_steps' => 'array',
        'night_steps' => 'array',
    ];

    public function audit()
    {
        return $this->belongsTo(Audit::class);
    }
}
