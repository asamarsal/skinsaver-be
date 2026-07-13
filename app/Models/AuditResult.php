<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class AuditResult extends Model
{
    use HasUuids;

    protected $fillable = [
        'audit_id',
        'product_id',
        'decision',
        'scores'
    ];

    protected $casts = [
        'scores' => 'array',
    ];

    public function audit()
    {
        return $this->belongsTo(Audit::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
