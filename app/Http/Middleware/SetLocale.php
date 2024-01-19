<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;

use App\Models\Language;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $lang = $request->query('lang');

        $isLangExist = Language::where('code', $lang)->where('deleted_by', null)->first();

        $isLangExist = $isLangExist ? $isLangExist->toArray() : null;

        if ($isLangExist) {
            App::setLocale($isLangExist['code']);
        } else {
            App::setLocale('ar');
        }

        return $next($request);
    }
}
