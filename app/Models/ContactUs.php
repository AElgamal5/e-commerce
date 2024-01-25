<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactUs extends Model
{
    use HasFactory;

    protected $table = 'contact_us';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'country_code',
        'title',
        'description',
    ];

    public function scopeSearch($query, $text)
    {
        return $query->where(function ($query) use ($text) {
            $query->where('name', 'like', "%$text%")
                ->orWhere('email', 'like', "%$text%")
                ->orWhere('phone', 'like', "%$text%")
                ->orWhere('country_code', 'like', "%$text%")
                ->orWhere('title', 'like', "%$text%")
                ->orWhere('description', 'like', "%$text%");
        });
    }
}
