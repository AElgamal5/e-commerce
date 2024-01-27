<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'status',
        'year',
        'price',
        'discount_type',
        'discount_value',
        'initial_quantity',
        'current_quantity',
        'category_id',

        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
    ];

    public function translations(): hasMany
    {
        return $this->hasMany(ProductTranslation::class, 'product_id');
    }

    public function tags(): hasMany
    {
        return $this->hasMany(ProductTag::class, 'product_id');
    }

    public function images(): hasMany
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }

    public function quantities(): hasMany
    {
        return $this->hasMany(ProductQuantity::class, 'product_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}
