<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BarberController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::get('/ping', function() {
    return ['pong' => true];
});

// Rotas Auth
Route::get('/401', [AuthController::class, 'anauthorized'])->name('login');
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout']);
Route::post('/auth/refresh', [AuthController::class, 'refresh']);
Route::post('/auth/create', [AuthController::class, 'create']);

// Rota User
Route::get('/user', [UserController::class, 'read']);
Route::post('/user', [UserController::class, 'update']);
Route::get('/user/favorites', [UserController::class, 'getFavorites']);
Route::post('/user/favorites', [UserController::class, 'addFavorite']);
Route::get('/user/appointments', [UserController::class, 'getAppointments']);

// Rotas barbers
Route::get('/barbers', [BarberController::class, 'list']);
Route::get('/barbers/{id}', [BarberController::class, 'one']);
Route::post('/barber/{id}/appointment', [BarberController::class, 'setAppointment']);

Route::get('/search', [BarberController::class, 'search']);

