<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class SkinScan extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'profile_id',
        'visual_notes',
        'skin_scores',
        'image_hash'
    ];

    protected $casts = [
        'visual_notes' => 'array',
        'skin_scores' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function beautyProfile()
    {
        return $this->belongsTo(BeautyProfile::class, 'profile_id');
    }
}
