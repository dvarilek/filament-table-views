<?php

namespace Dvarilek\FilamentTableViews\Tests\Models;

use Dvarilek\FilamentTableViews\Tests\database\factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }
}