<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;

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

/**
 * * COMANDO PARA GENERAR EL SERVICIO
 * * php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider
 * * php artisan jwt:secret
 * * en el modelo agregamos el implements y las funciones del fondo
 * * Modificamos el auth de la carpeta config
 */

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([
    'middlewware' => 'api',
    'prefix' => 'auth',
], function ($router) {
    Route::post('login', [UserController::class, 'login']);
    Route::post('register', [UserController::class, 'registerUser']);
});

