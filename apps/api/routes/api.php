<?php

use App\Http\Controllers\CatalogueController;
use App\Http\Controllers\PrototypeDocumentController;
use App\Http\Controllers\StarterDocumentController;
use App\Http\Controllers\ValidateDocumentController;
use App\Http\Middleware\LimitDocumentBody;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('v1.')->group(function (): void {
    Route::get('catalogues/{catalogue}', [CatalogueController::class, 'show'])->name('catalogues.show');
    Route::get('starters/sandbox', StarterDocumentController::class)->name('starters.sandbox');
    Route::get('prototypes/basketball-brick', PrototypeDocumentController::class)->name('prototypes.basketball-brick');
    Route::post('documents/validate', ValidateDocumentController::class)
        ->middleware([LimitDocumentBody::class, 'throttle:60,1'])
        ->name('documents.validate');
});

Route::get('/health', function (): JsonResponse {
    try {
        DB::select('select 1');

        return response()->json(['status' => 'ok', 'database' => 'connected']);
    } catch (Throwable $exception) {
        report($exception);

        return response()->json(['status' => 'unavailable', 'database' => 'unavailable'], 503);
    }
});
