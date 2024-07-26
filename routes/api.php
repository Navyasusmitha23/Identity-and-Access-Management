<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiController;

// Open Routes 
Route::post("register", [ApiController::class, "register"]);
Route::post("login", [ApiController::class, "login"]);

// Token Introspection Route
Route::post('/oauth/introspect', [ApiController::class, 'introspectToken']);

// Protected Routes
Route::group([
    "middleware" => ["auth:api"]
], function() {
    Route::get("profile", [ApiController::class, "profile"]);
    Route::get("logout", [ApiController::class, "logout"]);
    Route::get("verify-token", [ApiController::class, "verifyToken"]);
});
