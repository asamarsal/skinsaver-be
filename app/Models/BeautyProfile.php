<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class BeautyProfile extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'skin_type',
        'concerns',
        'sensitivities',
        'budget_tier'
    ];

    protected $casts = [
        'concerns' => 'array',
        'sensitivities' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function skinScans()
    {
        return $this->hasMany(SkinScan::class, 'profile_id');
    }
}
