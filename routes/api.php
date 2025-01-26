<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegistryController;

Route::post('/add', [RegistryController::class, 'add']);
Route::delete('/remove', [RegistryController::class, 'remove']);
Route::get('/check/{item}', [RegistryController::class, 'check']);
Route::post('/diff', [RegistryController::class, 'diff']);
Route::put('/invert', [RegistryController::class, 'invert']);
