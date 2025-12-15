<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProgramController;

Route::middleware(['auth', 'role:admin,konsultan'])->group(function () {
  // ... route resource program ...
  // Observasi/Evaluasi dari tabel assessments (lama)
  Route::get('/program/riwayat/{anakDidikId}', [ProgramController::class, 'riwayatObservasi'])->name('program.riwayat');
  Route::delete('/program/{assessment}', [ProgramController::class, 'destroyObservasi'])->name('program.riwayat.destroy');

  // Observasi/Evaluasi dari tabel programs (baru)
  Route::get('/program/riwayat-observasi-program/{anakDidikId}', [ProgramController::class, 'riwayatObservasiProgram'])->name('program.riwayat-observasi-program');
  Route::get('/program/observasi-program/{id}', [ProgramController::class, 'showObservasiProgram'])->name('program.observasi-program.show');
  Route::delete('/program/observasi-program/{id}', [ProgramController::class, 'destroyObservasiProgram'])->name('program.observasi-program.destroy');

  // Export PDF for ProgramWicara (observasi/evaluasi)
  Route::get('/program/{id}/export-pdf', [ProgramController::class, 'exportPdf'])->name('program.export-pdf');
});
