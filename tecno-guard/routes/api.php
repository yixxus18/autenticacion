<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

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

Route::middleware('auth:api')->prefix('v1')->group(function () {
    Route::get('/me', [ProfileController::class, 'me']);
    Route::put('/profile', [ProfileController::class, 'updateProfile']);

    Route::post('/logout', function (Request $request) {
        $request->user()->token()->revoke();
        DB::table('oauth_refresh_tokens')
            ->where('access_token_id', $request->user()->token()->id)
            ->update(['revoked' => true]);

        return response()->json(['message' => 'Logged out successfully']);
    });
});

