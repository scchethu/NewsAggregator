<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\UserPreferenceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('register', [AuthController::class, 'register']);
Route::post('password/email', [AuthController::class, 'sendResetLink']);
Route::post('password/reset', [AuthController::class, 'resetPassword']);



Route::group(['middleware' => 'auth:sanctum'], function() {
    Route::get('logout', [AuthController::class, 'logout']);
    Route::get('user', [AuthController::class, 'user']);

    Route::get('articles', [ArticleController::class, 'index']);
    Route::get('articles/{id}', [ArticleController::class, 'show']);
    Route::get('/personalized-feed', [UserPreferenceController::class, 'getPersonalizedFeed']);
    Route::post('/preferences', [UserPreferenceController::class, 'setPreference']);
    Route::get('/authors', [UserPreferenceController::class, 'getAuthors']);
    Route::get('/categories', [UserPreferenceController::class, 'getCategories']);
    Route::get('/news-sources', [UserPreferenceController::class, 'getNewsSources']);
});

