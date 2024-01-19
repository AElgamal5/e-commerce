<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ColorTranslation extends Model
{
    use HasFactory;

    protected $table = 'colors_translations';

    protected $fillable = [
        'name',
        'color_id',
        'language_id',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
    ];

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'language_id');
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class, 'language_id');
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
