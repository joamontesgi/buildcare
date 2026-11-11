<?php

use App\Http\Controllers\Api\MatriculadoController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/matriculados', [MatriculadoController::class, 'index']);
Route::post('/matriculados/import', [MatriculadoController::class, 'import']);
