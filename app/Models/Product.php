<?php
// app/Models/Product.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'sku',
        'unit_cost',
        'stock',
        'category',
        'notes',
        'image_path',
    ];

    /**
     * The invoices that belong to the product.
     */
    public function invoices(): BelongsToMany
    {
        return $this->belongsToMany(Invoice::class)
            ->withPivot('quantity', 'unit_cost', 'total_cost')
            ->withTimestamps();
    }
}
