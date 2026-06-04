<?php

use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/status', function () {
    return response()->json([
        'message' => 'API SalesCore funcionando',
        'status' => 'ok'
    ]);
});

Route::apiResource('products', ProductController::class);