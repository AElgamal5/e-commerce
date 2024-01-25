<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use function Laravel\Prompts\text;

class Color extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'created_by',
        'updated_by',
        'deleted_by',
        'deleted_at',
    ];

    public function saveTranslations($input)
    {
        foreach ($input['translations'] as $trans) {
            $this->translations()->create([
                'language_id' => $trans['languageId'],
                'name' => $trans['name'],
                'created_by' => $input['created_by'],
            ]);
        }
    }

    public function updateTranslations($input)
    {
        foreach ($input['translations'] as $trans) {
            $translation = $this->translations()
                ->where('language_id', $trans['languageId'])
                ->first();

            //if not exist , else if deleted, else need to update
            if (!$translation) {
                $this->translations()->create([
                    'language_id' => $trans['languageId'],
                    'name' => $trans['name'],
                    'created_by' => $input['updated_by'],
                ]);
            } elseif (!is_null($translation->deleted_by)) {
                $translation->update([
                    'name' => $trans['name'],
                    'updated_by' => $input['updated_by'],
                    'deleted_by' => null,
                    'deleted_at' => null,
                ]);
            } else {
                $translation->update([
                    'name' => $trans['name'],
                    'updated_by' => $input['updated_by']
                ]);
            }
        }
    }

    public function deleteTranslations($input)
    {
        $this->translations()->update($input);
    }

    public function scopeSearch($query, $text)
    {
        return $query->where(function ($query) use ($text) {
            $query->where('code', 'like', "%$text%")
                ->orWhereHas('translations', function ($query) use ($text) {
                    $query->where('name', 'like', "%$text%");
                });
        });
    }

    public function translations(): hasMany
    {
        return $this->hasMany(ColorTranslation::class, 'color_id');
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
