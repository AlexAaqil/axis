<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\YearController;
use App\Http\Controllers\Api\V1\QuarterController;
use App\Http\Controllers\Api\V1\ObjectiveController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::get('years', [YearController::class, 'index'])->name('years.index');
    Route::post('years', [YearController::class, 'store'])->name('years.store');
    Route::get('years/{year}', [YearController::class, 'show'])->name('years.show');
    Route::put('years/{year}', [YearController::class, 'update'])->name('years.update');
    Route::delete('years/{year}', [YearController::class, 'destroy'])->name('years.destroy');

    Route::get('quarters', [QuarterController::class, 'index'])->name('quarters.index');
    Route::post('quarters', [QuarterController::class, 'store'])->name('quarters.store');
    Route::get('quarters/{quarter}', [QuarterController::class, 'show'])->name('quarters.show');
    Route::put('quarters/{quarter}', [QuarterController::class, 'update'])->name('quarters.update');
    Route::delete('quarters/{quarter}', [QuarterController::class, 'destroy'])->name('quarters.destroy');
    Route::get('years/{year}/quarters', [QuarterController::class, 'getByYear'])->name('years.quarters.index');

        // Additional utility routes
    Route::post('objectives/reorder', [ObjectiveController::class, 'reorder'])->name('objectives.reorder');
    Route::post('objectives/bulk-status', [ObjectiveController::class, 'bulkStatusUpdate'])->name('objectives.bulk-status');
    Route::get('objectives/kanban', [ObjectiveController::class, 'kanban'])->name('objectives.kanban');

    Route::get('objectives', [ObjectiveController::class, 'index'])->name('objectives.index');
    Route::post('objectives', [ObjectiveController::class, 'store'])->name('objectives.store');
    Route::get('objectives/{objective:uuid}', [ObjectiveController::class, 'show'])->name('objectives.show');
    Route::put('objectives/{objective:uuid}', [ObjectiveController::class, 'update'])->name('objectives.update');
    Route::delete('objectives/{objective:uuid}', [ObjectiveController::class, 'destroy'])->name('objectives.destroy');
});
