<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\api\v1\LanguageController;
use App\Http\Controllers\api\v1\UserController;
use App\Http\Controllers\api\v1\AuthController;
use App\Http\Controllers\api\v1\SizeController;
use App\Http\Controllers\api\v1\ColorController;
use App\Http\Controllers\api\v1\CategoryController;
use App\Http\Controllers\api\v1\TagController;
use App\Http\Controllers\api\v1\ProductController;
use App\Http\Controllers\api\v1\ContactUsController;
use App\Http\Controllers\api\v1\AdvertisementController;

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
        Route::group(['prefix' => 'contactUs'], function () {
            Route::post('/', [ContactUsController::class, 'store']);
        });

        //protected
        Route::group(['middleware' => 'auth:sanctum'], function () {

            Route::group(['prefix' => 'auth'], function () {
                Route::get('/logout', [AuthController::class, 'logout']);
            });

            Route::group(['prefix' => 'users'], function () {
                Route::get('/', [UserController::class, 'index']);
                Route::post('/', [UserController::class, 'store']);
                Route::get('/{user}', [UserController::class, 'show']);
                Route::patch('/{user}', [UserController::class, 'update']);
                Route::delete('/{user}', [UserController::class, 'destroy']);
            });

            Route::group(['prefix' => 'languages'], function () {
                Route::get('/', [LanguageController::class, 'index']);
                Route::post('/', [LanguageController::class, 'store']);
                Route::get('/{language}', [LanguageController::class, 'show']);
                Route::patch('/{language}', [LanguageController::class, 'update']);
                Route::delete('/{language}', [LanguageController::class, 'destroy']);
            });

            Route::group(['prefix' => 'sizes'], function () {
                Route::get('/', [SizeController::class, 'index']);
                Route::post('/', [SizeController::class, 'store']);
                Route::get('/{size}', [SizeController::class, 'show']);
                Route::patch('/{size}', [SizeController::class, 'update']);
                Route::delete('/{size}', [SizeController::class, 'destroy']);
            });

            Route::group(['prefix' => 'colors'], function () {
                Route::get('/', [ColorController::class, 'index']);
                Route::post('/', [ColorController::class, 'store']);
                Route::get('/{color}', [ColorController::class, 'show']);
                Route::patch('/{color}', [ColorController::class, 'update']);
                Route::delete('/{color}', [ColorController::class, 'destroy']);
            });

            Route::group(['prefix' => 'categories'], function () {
                Route::get('/', [CategoryController::class, 'index']);
                Route::post('/', [CategoryController::class, 'store']);
                Route::get('/{category}', [CategoryController::class, 'show']);
                Route::patch('/{category}', [CategoryController::class, 'update']);
                Route::delete('/{category}', [CategoryController::class, 'destroy']);
            });

            Route::group(['prefix' => 'tags'], function () {
                Route::get('/', [TagController::class, 'index']);
                Route::post('/', [TagController::class, 'store']);
                Route::get('/{tag}', [TagController::class, 'show']);
                Route::patch('/{tag}', [TagController::class, 'update']);
                Route::delete('/{tag}', [TagController::class, 'destroy']);
            });

            Route::group(['prefix' => 'contactUs'], function () {
                Route::get('/', [ContactUsController::class, 'index']);
                Route::get('/{contactUs}', [ContactUsController::class, 'show']);
                Route::delete('/{contactUs}', [ContactUsController::class, 'destroy']);
            });

            Route::group(['prefix' => 'advertisements'], function () {
                Route::get('/', [AdvertisementController::class, 'index']);
                Route::post('/', [AdvertisementController::class, 'store']);
                Route::get('/{advertisement}', [AdvertisementController::class, 'show']);
                Route::patch('/{advertisement}', [AdvertisementController::class, 'update']);
                Route::delete('/{advertisement}', [AdvertisementController::class, 'destroy']);
            });

            Route::group(['prefix' => 'products'], function () {
                Route::get('/', [ProductController::class, 'index']);
                Route::post('/', [ProductController::class, 'store']);
                Route::get('/{product}', [ProductController::class, 'show']);
                Route::patch('/{product}', [ProductController::class, 'update']);
                Route::delete('/{product}', [ProductController::class, 'destroy']);

                Route::post('/{product}/images/add', [ProductController::class, 'addImages']);
                Route::delete('/{product}/images/delete', [ProductController::class, 'deleteImages']);

                Route::post('/{product}/quantities/add', [ProductController::class, 'addQuantities']);
                Route::delete('/{product}/quantities/delete', [ProductController::class, 'deleteQuantities']);
            });
        });

    });

});
