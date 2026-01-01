<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProgramController;

// Routes that allow viewing riwayat and detail should be accessible to terapis as well
Route::middleware(['auth', 'role:admin,konsultan,terapis,guru'])->group(function () {
  // Observasi/Evaluasi dari tabel assessments (lama) - view only
  Route::get('/program/riwayat/{anakDidikId}', [ProgramController::class, 'riwayatObservasi'])->name('program.riwayat');

  // Observasi/Evaluasi dari tabel programs (baru) - view only
  Route::get('/program/riwayat-observasi-program/{anakDidikId}', [ProgramController::class, 'riwayatObservasiProgram'])->name('program.riwayat-observasi-program');
  // Program anak riwayat (programs assigned by konsultan)
  Route::get('/program-anak/riwayat-program/{anakDidikId}', [App\Http\Controllers\ProgramAnakController::class, 'riwayatProgram'])->name('program-anak.riwayat-program');
  // Program anak detail JSON for modal
  Route::get('/program-anak/{id}/json', [App\Http\Controllers\ProgramAnakController::class, 'showJson'])->name('program-anak.show.json');
  // All programs for an anak as JSON
  Route::get('/program-anak/{anakDidikId}/all-json', [App\Http\Controllers\ProgramAnakController::class, 'showAllForAnak'])->name('program-anak.all.json');
  // programs for anak filtered by konsultan
  Route::get('/program-anak/riwayat-program/{anakDidikId}/konsultan/{konsultanId}', [App\Http\Controllers\ProgramAnakController::class, 'riwayatProgramByKonsultan'])->name('program-anak.riwayat-program.konsultan');
  // programs for anak filtered by konsultan and date (YYYY-MM-DD)
  Route::get('/program-anak/riwayat-program/{anakDidikId}/konsultan/{konsultanId}/date/{date}', [App\Http\Controllers\ProgramAnakController::class, 'riwayatProgramByKonsultanAndDate'])->name('program-anak.riwayat-program.konsultan.date');
  // JSON update/delete endpoints for ProgramAnak (AJAX)
  Route::put('/program-anak/{id}/update-json', [App\Http\Controllers\ProgramAnakController::class, 'updateJson'])->name('program-anak.update.json');
  Route::delete('/program-anak/{id}/delete-json', [App\Http\Controllers\ProgramAnakController::class, 'destroyJson'])->name('program-anak.delete.json');

  // set/unset suggestion flag for all programs of a konsultan on a specific date
  Route::put('/program-anak/{anakDidikId}/konsultan/{konsultanId}/date/{date}/suggest', [App\Http\Controllers\ProgramAnakController::class, 'setSuggestForGroup'])->name('program-anak.suggest.group');
  Route::get('/program/observasi-program/{id}', [ProgramController::class, 'showObservasiProgram'])->name('program.observasi-program.show');
  Route::get('/program/observasi-program/{sumber}/{id}', [ProgramController::class, 'showObservasiProgram'])->name('program.observasi-program.show.withsumber');
  // Edit / Update endpoints for consultants to edit their own observations
  Route::get('/program/observasi-program/{id}/edit', [ProgramController::class, 'editObservasiProgram'])->name('program.observasi-program.edit');
  Route::get('/program/observasi-program/{sumber}/{id}/edit', [ProgramController::class, 'editObservasiProgram'])->name('program.observasi-program.edit.withsumber');
  Route::put('/program/observasi-program/{id}', [ProgramController::class, 'updateObservasiProgram'])->name('program.observasi-program.update');
  Route::put('/program/observasi-program/{sumber}/{id}', [ProgramController::class, 'updateObservasiProgram'])->name('program.observasi-program.update.withsumber');
  // Export PDF for ProgramWicara (observasi/evaluasi)
  Route::get('/program/{id}/export-pdf', [ProgramController::class, 'exportPdf'])->name('program.export-pdf');
});

// Routes that perform destructive actions remain restricted to admin/konsultan only
Route::middleware(['auth', 'role:admin,konsultan'])->group(function () {
  Route::delete('/program/{assessment}', [ProgramController::class, 'destroyObservasi'])->name('program.riwayat.destroy');
  Route::delete('/program/observasi-program/{id}', [ProgramController::class, 'destroyObservasiProgram'])->name('program.observasi-program.destroy');
});
