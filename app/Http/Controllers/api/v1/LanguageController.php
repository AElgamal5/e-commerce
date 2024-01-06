<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Language;

class LanguageController extends Controller
{
    public function index()
    {
        return 'from index';
    }

    public function store()
    {
        return 'from store';
    }

    public function show(string $id)
    {
        return 'from show' . $id;
    }

    public function update(string $id)
    {
        return 'from update' . $id;
    }

    public function destroy(string $id)
    {
        return 'from destroy' . $id;
    }
}
