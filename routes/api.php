<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\api\v1\LanguageController;
use App\Http\Controllers\api\v1\UserController;
use App\Http\Controllers\api\v1\AuthController;
use App\Http\Controllers\api\v1\SizeController;
use App\Http\Controllers\api\v1\ColorController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


//test api
Route::get('/', function (Request $request) {
    return response()->json([
        'msg' => __('InTheNameOfAllah')
    ]);
})->middleware('setLocale');


Route::group(['prefix' => 'v1'], function () {

    Route::middleware(['setLocale'])->group(function () {

        //public
        Route::group(['prefix' => 'auth'], function () {
            Route::post('/login', [AuthController::class, 'login']);
            Route::post('/signup', [AuthController::class, 'signup']);
        });

        //protected
        Route::group(['middleware' => 'auth:sanctum'], function () {
            Route::group(['prefix' => 'users'], function () {
                Route::get('/', [UserController::class, 'index']);
                Route::post('/', [UserController::class, 'store'])->middleware('store');
                Route::get('/{user}', [UserController::class, 'show']);
                Route::patch('/{user}', [UserController::class, 'update'])->middleware('update');
                Route::delete('/{user}', [UserController::class, 'destroy'])->middleware('destroy');
            });

            Route::group(['prefix' => 'languages'], function () {
                Route::get('/', [LanguageController::class, 'index']);
                Route::post('/', [LanguageController::class, 'store'])->middleware('store');
                Route::get('/{language}', [LanguageController::class, 'show']);
                Route::patch('/{language}', [LanguageController::class, 'update'])->middleware('update');
                Route::delete('/{language}', [LanguageController::class, 'destroy'])->middleware('destroy');
            });

            Route::group(['prefix' => 'sizes'], function () {
                Route::get('/', [SizeController::class, 'index']);
                Route::post('/', [SizeController::class, 'store'])->middleware('store');
                Route::get('/{size}', [SizeController::class, 'show']);
                Route::patch('/{size}', [SizeController::class, 'update'])->middleware('update');
                Route::delete('/{size}', [SizeController::class, 'destroy'])->middleware('destroy');
            });

            Route::group(['prefix' => 'colors'], function () {
                Route::get('/', [ColorController::class, 'index']);
                Route::post('/', [ColorController::class, 'store'])->middleware('store');
                Route::get('/{color}', [ColorController::class, 'show']);
                Route::patch('/{color}', [ColorController::class, 'update'])->middleware('update');
                Route::delete('/{color}', [ColorController::class, 'destroy'])->middleware('destroy');
            });
        });
    });
});
