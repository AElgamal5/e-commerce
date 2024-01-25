<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Advertisement extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'link',
        'status',

        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
    ];

    public function scopeSearch($query, $text)
    {
        return $query->where(function ($query) use ($text) {
            $query->where('name', 'like', "%$text%")
                ->orWhere('link', 'like', "%$text%");
        });
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
