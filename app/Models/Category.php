<?php

namespace App\Models;

use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'image',
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
                'description' => $trans['description'] ?? null,
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

            //if not exist , else if deleted then respawn, else need to update
            if (!$translation) {
                //need at least name to create a new translation
                if (!isset($trans['name'])) {
                    return response()
                        ->json(
                            ['message' => "Name is required to create a new category translations"],
                            Response::HTTP_UNPROCESSABLE_ENTITY
                        );
                }

                $this->translations()->create([
                    'language_id' => $trans['languageId'],
                    'name' => $trans['name'],
                    'description' => $trans['description'] ?? null,
                    'created_by' => $input['updated_by'],
                ]);
            } elseif (!is_null($translation->deleted_by)) {
                $translation->update([
                    'name' => $trans['name'] ?? $translation->name,
                    'description' => $trans['description'] ?? $translation->description,
                    'updated_by' => $input['updated_by'],
                    'deleted_by' => null,
                    'deleted_at' => null,
                ]);
            } else {
                $translation->update([
                    'name' => $trans['name'] ?? $translation->name,
                    'description' => $trans['description'] ?? $translation->description,
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
            $query->WhereHas('translations', function ($query) use ($text) {
                $query->where('name', 'like', "%$text%")
                    ->orWhere('description', 'like', "%$text%");
            });
        });
    }

    public function translations(): hasMany
    {
        return $this->hasMany(CategoryTranslation::class, 'category_id');
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
