<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/health', function (): JsonResponse {
    try {
        DB::select('select 1');

        return response()->json(['status' => 'ok', 'database' => 'connected']);
    } catch (Throwable $exception) {
        report($exception);

        return response()->json(['status' => 'unavailable', 'database' => 'unavailable'], 503);
    }
});
