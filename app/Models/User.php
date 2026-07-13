<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['wallet_address', 'auth_level'])]
#[Hidden(['remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, HasUuids;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [];
    }
    
    // Relationships
    public function beautyProfile()
    {
        return $this->hasOne(BeautyProfile::class);
    }
    
    public function skinScans()
    {
        return $this->hasMany(SkinScan::class);
    }
    
    public function audits()
    {
        return $this->hasMany(Audit::class);
    }
    
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
