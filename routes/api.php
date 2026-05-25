<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->get('/status', function (Request $request) {
    return response()->json(['status' => 'ok']);
});
