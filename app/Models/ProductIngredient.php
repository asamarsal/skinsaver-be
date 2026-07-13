<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ProductIngredient extends Model
{
    use HasUuids;

    protected $fillable = [
        'product_id',
        'canonical_name',
        'functions',
        'flags'
    ];

    protected $casts = [
        'functions' => 'array',
        'flags' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
