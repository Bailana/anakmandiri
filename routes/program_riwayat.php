<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProgramController;

Route::middleware(['auth', 'role:admin,konsultan'])->group(function () {
  // ... route resource program ...
  Route::get('/program/riwayat/{anakDidikId}', [ProgramController::class, 'riwayatObservasi'])->name('program.riwayat');
  Route::delete('/program/{assessment}', [ProgramController::class, 'destroyObservasi'])->name('program.riwayat.destroy');
});
