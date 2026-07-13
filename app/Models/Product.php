<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Product extends Model
{
    use HasUuids;

    protected $fillable = [
        'brand',
        'name',
        'category',
        'inci_list'
    ];

    protected $casts = [
        'inci_list' => 'array',
    ];

    public function ingredients()
    {
        return $this->hasMany(ProductIngredient::class);
    }
}
