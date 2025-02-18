<?php

use Illuminate\Support\Facades\Route;

// Swagger documentation routes
Route::get('docs/api-docs.json', function () {
    $filePath = storage_path('api-docs/api-docs.json');
    if (!file_exists($filePath)) {
        return response()->json(['message' => 'API documentation not found'], 404);
    }
    return response()->file($filePath, ['Content-Type' => 'application/json']);
});

Route::get('/', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Welcome to the API'
    ]);
});
