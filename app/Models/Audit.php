<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Audit extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'audit_type',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function results()
    {
        return $this->hasMany(AuditResult::class);
    }

    public function routine()
    {
        return $this->hasOne(Routine::class);
    }
}
