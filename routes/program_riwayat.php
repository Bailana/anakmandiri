<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProgramController;

// Routes that allow viewing riwayat and detail should be accessible to terapis as well
Route::middleware(['auth', 'role:admin,konsultan,terapis'])->group(function () {
  // Observasi/Evaluasi dari tabel assessments (lama) - view only
  Route::get('/program/riwayat/{anakDidikId}', [ProgramController::class, 'riwayatObservasi'])->name('program.riwayat');

  // Observasi/Evaluasi dari tabel programs (baru) - view only
  Route::get('/program/riwayat-observasi-program/{anakDidikId}', [ProgramController::class, 'riwayatObservasiProgram'])->name('program.riwayat-observasi-program');
  Route::get('/program/observasi-program/{id}', [ProgramController::class, 'showObservasiProgram'])->name('program.observasi-program.show');
  Route::get('/program/observasi-program/{sumber}/{id}', [ProgramController::class, 'showObservasiProgram'])->name('program.observasi-program.show.withsumber');
  // Export PDF for ProgramWicara (observasi/evaluasi)
  Route::get('/program/{id}/export-pdf', [ProgramController::class, 'exportPdf'])->name('program.export-pdf');
});

// Routes that perform destructive actions remain restricted to admin/konsultan only
Route::middleware(['auth', 'role:admin,konsultan'])->group(function () {
  Route::delete('/program/{assessment}', [ProgramController::class, 'destroyObservasi'])->name('program.riwayat.destroy');
  Route::delete('/program/observasi-program/{id}', [ProgramController::class, 'destroyObservasiProgram'])->name('program.observasi-program.destroy');
});
