<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\dashboard\Analytics;
use App\Http\Controllers\dashboard\AdminDashboard;
use App\Http\Controllers\dashboard\GuruDashboard;
use App\Http\Controllers\dashboard\KonsultanDashboard;
use App\Http\Controllers\dashboard\TerapisDashboard;
use App\Http\Controllers\layouts\WithoutMenu;
use App\Http\Controllers\layouts\WithoutNavbar;
use App\Http\Controllers\layouts\Fluid;
use App\Http\Controllers\layouts\Container;
use App\Http\Controllers\layouts\Blank;
use App\Http\Controllers\pages\AccountSettingsAccount;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\pages\AccountSettingsNotifications;
use App\Http\Controllers\pages\AccountSettingsConnections;
use App\Http\Controllers\pages\MiscError;
use App\Http\Controllers\pages\MiscUnderMaintenance;
use App\Http\Controllers\authentications\LoginBasic;
use App\Http\Controllers\authentications\RegisterBasic;
use App\Http\Controllers\authentications\ForgotPasswordBasic;
use App\Http\Controllers\cards\CardBasic;
use App\Http\Controllers\user_interface\Accordion;
use App\Http\Controllers\user_interface\Alerts;
use App\Http\Controllers\user_interface\Badges;
use App\Http\Controllers\user_interface\Buttons;
use App\Http\Controllers\user_interface\Carousel;
use App\Http\Controllers\user_interface\Collapse;
use App\Http\Controllers\user_interface\Dropdowns;
use App\Http\Controllers\user_interface\Footer;
use App\Http\Controllers\user_interface\ListGroups;
use App\Http\Controllers\user_interface\Modals;
use App\Http\Controllers\user_interface\Navbar;
use App\Http\Controllers\user_interface\Offcanvas;
use App\Http\Controllers\user_interface\PaginationBreadcrumbs;
use App\Http\Controllers\user_interface\Progress;
use App\Http\Controllers\user_interface\Spinners;
use App\Http\Controllers\user_interface\TabsPills;

// use App\Http\Controllers\BroadcastTestController;
// // Route untuk mengirim notifikasi broadcast (uji coba)
// Route::get('/broadcast-test', [BroadcastTestController::class, 'kirimNotifikasi']);

use App\Http\Controllers\user_interface\Toasts;
use App\Http\Controllers\user_interface\TooltipsPopovers;
use App\Http\Controllers\user_interface\Typography;
use App\Http\Controllers\extended_ui\PerfectScrollbar;
use App\Http\Controllers\extended_ui\TextDivider;
use App\Http\Controllers\icons\RiIcons;
use App\Http\Controllers\form_elements\BasicInput;
use App\Http\Controllers\form_elements\InputGroups;
use App\Http\Controllers\form_layouts\VerticalForm;
use App\Http\Controllers\form_layouts\HorizontalForm;
use App\Http\Controllers\tables\Basic as TablesBasic;
use App\Http\Controllers\AuthController;

// Redirect home to dashboard or login
Route::get('/', function () {
  if (Auth::check()) {
    $user = Auth::user();
    switch ($user->role) {
      case 'admin':
        return redirect()->route('dashboard-admin');
      case 'guru':
        return redirect()->route('dashboard-guru');
      case 'terapis':
        return redirect()->route('dashboard-terapis');
      case 'konsultan':
        return redirect()->route('dashboard-konsultan');
      default:
        // If role not recognized, fall back to login
        Auth::logout();
        return redirect()->route('login');
    }
  }
  return redirect()->route('login');
});

// Authentication Routes
Route::middleware(['guest'])->group(function () {
  Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
  Route::post('/login', [AuthController::class, 'login'])->name('login.post');

  // Forgot Password Routes
  Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
  Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');

  // Reset Password Routes (handled outside 'guest' so links from email work even if user is authenticated)
  // NOTE: routes are defined below outside the 'guest' middleware group.
});

// Reset Password Routes available to all (so email link opens the reset form even when user is logged in)
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

Route::middleware(['auth'])->group(function () {
  Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

  // Profile Routes
  Route::get('/my-profile', [ProfileController::class, 'show'])->name('profile.show');
  Route::put('/my-profile', [ProfileController::class, 'update'])->name('profile.update');
  Route::put('/my-profile/password', [ProfileController::class, 'updatePassword'])->name('profile.updatePassword');
});

Route::get('/auth/register-basic', [RegisterBasic::class, 'index'])->name('auth-register-basic');
Route::get('/auth/forgot-password-basic', [AuthController::class, 'showForgotPassword'])->name('auth-reset-password-basic');

Route::middleware(['auth', 'role:terapis'])->get('/dashboard-terapis', [App\Http\Controllers\dashboard\TerapisDashboard::class, 'index'])->name('dashboard-terapis');

Route::middleware(['auth', 'role:konsultan'])->get('/dashboard-konsultan', [App\Http\Controllers\dashboard\KonsultanDashboard::class, 'index'])->name('dashboard-konsultan');

// Dashboard khusus admin
Route::middleware(['auth', 'role:admin'])->get('/dashboard', [App\Http\Controllers\dashboard\AdminDashboard::class, 'index'])->name('dashboard-admin');

// Dashboard khusus guru
Route::middleware(['auth', 'role:guru'])->get('/dashboard-guru', [App\Http\Controllers\dashboard\GuruDashboard::class, 'index'])->name('dashboard-guru');

// AJAX endpoints for guru dashboard filters
Route::middleware(['auth', 'role:guru'])->get('/dashboard-guru/programs-for-anak/{anakId}', [App\Http\Controllers\dashboard\GuruDashboard::class, 'programsForAnak'])->name('dashboard-guru.programs-for-anak');
Route::middleware(['auth', 'role:guru'])->get('/dashboard-guru/chart-data', [App\Http\Controllers\dashboard\GuruDashboard::class, 'chartDataForAnak'])->name('dashboard-guru.chart-data');

// Protected Routes
Route::middleware(['auth'])->group(function () {

  Route::get('anak-didik', [App\Http\Controllers\AnakDidikController::class, 'index'])->name('anak-didik.index');
  // Resource route harus sebelum show manual agar edit tidak bentrok
  Route::middleware(['auth', 'role:admin'])->group(function () {
    // Activity Logs (admin only)
    Route::get('activity-logs', [App\Http\Controllers\ActivityLogController::class, 'index'])->name('activity.logs');
    Route::get('activity-logs/export', [App\Http\Controllers\ActivityLogController::class, 'export'])->name('activity.logs.export');
    Route::resource('anak-didik', 'App\Http\Controllers\AnakDidikController')->except(['index', 'show', 'show']);
  });
  Route::patch('anak-didik/{anak_didik}/status', [App\Http\Controllers\AnakDidikController::class, 'updateStatus'])->name('anak-didik.update-status');
  Route::get('anak-didik/{anak_didik}', [App\Http\Controllers\AnakDidikController::class, 'show'])
    ->where('anak_didik', '[0-9]+')
    ->name('anak-didik.show');
  Route::get('anak-didik/{anak_didik}/export-pdf', [App\Http\Controllers\AnakDidikController::class, 'exportPdf'])->name('anak-didik.export-pdf');


  // Karyawan, Konsultan: tetap admin saja. Program: admin full, konsultan create/index/show/store
  Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('karyawan', 'App\Http\Controllers\KaryawanController');
    Route::resource('konsultan', 'App\Http\Controllers\KonsultanDataController');
    Route::resource('program', 'App\Http\Controllers\ProgramController');
    Route::post('program/{id}/approve', [App\Http\Controllers\ProgramController::class, 'approve'])->name('program.approve');
    Route::resource('pengguna', 'App\Http\Controllers\PenggunaController');
    // Kedisiplinan - admin-only routes removed from here so we can allow guru access separately
  });
  // Allow konsultan, terapis, admin, and guru to view program index/show (read-only for guru)
  Route::middleware(['auth', 'role:admin,konsultan,terapis,guru'])->group(function () {
    Route::get('program/{id}/export-pdf', [App\Http\Controllers\ProgramController::class, 'exportPdf'])->name('program.export-pdf');
    Route::resource('program', 'App\Http\Controllers\ProgramController')->only(['index', 'show']);
  });
  // Kedisiplinan: allow both admin and guru to access the index and riwayat
  Route::middleware(['auth', 'role:admin,guru'])->group(function () {
    Route::get('kedisiplinan', [App\Http\Controllers\KedisiplinanController::class, 'index'])->name('kedisiplinan.index');
    Route::get('kedisiplinan/{guru}/riwayat', [App\Http\Controllers\KedisiplinanController::class, 'riwayat'])->name('kedisiplinan.riwayat');
  });
  Route::middleware(['auth', 'role:konsultan'])->group(function () {
    Route::resource('program', 'App\Http\Controllers\ProgramController')->only(['create', 'store']);
  });

  // Assessment: admin & guru (guru hanya index/show)
  Route::middleware(['auth', 'role:admin,guru'])->group(function () {
    Route::get('assessment/ppi-programs', [App\Http\Controllers\AssessmentController::class, 'ppiPrograms'])->name('assessment.ppi-programs');
    // Program history per anak (used for small charts on index)
    Route::get('assessment/{anakId}/program-history', [App\Http\Controllers\AssessmentController::class, 'programHistory'])->name('assessment.program-history');
    // Blokir akses admin ke /assessment/create
    Route::get('assessment/create', function () {
      if (auth()->check() && auth()->user()->role === 'admin') {
        abort(403, 'Halaman ini tidak dapat diakses oleh admin.');
      }
      return app(App\Http\Controllers\AssessmentController::class)->create();
    })->name('assessment.create');
    Route::resource('assessment', 'App\\Http\\Controllers\\AssessmentController')->except(['create']);
    Route::get('assessment/{id}/export-pdf', [App\Http\Controllers\AssessmentController::class, 'exportPdf'])->name('assessment.export-pdf');
  });

  Route::middleware(['auth', 'role:admin,konsultan'])->group(function () {
    Route::post('program-anak/program-konsultan', [App\Http\Controllers\ProgramAnakController::class, 'storeProgramKonsultan'])->name('program-anak.program-konsultan.store');
    Route::get('program-anak/psikologi-latest/{anakId}', [App\Http\Controllers\ProgramAnakController::class, 'latestPsikologiForAnak'])->name('program-anak.psikologi-latest');
    Route::get('program-anak/daftar-program', [App\Http\Controllers\ProgramAnakController::class, 'daftarProgramKonsultan'])->name('program-anak.daftar-program');
    Route::put('program-anak/program-konsultan/{id}', [App\Http\Controllers\ProgramAnakController::class, 'updateProgramKonsultan'])->name('program-anak.program-konsultan.update');
    Route::delete('program-anak/program-konsultan/{id}', [App\Http\Controllers\ProgramAnakController::class, 'destroyProgramKonsultan'])->name('program-anak.program-konsultan.destroy');
    Route::resource('program-anak', App\Http\Controllers\ProgramAnakController::class)->except(['index', 'show']);
  });
  Route::middleware(['auth', 'role:admin,konsultan,guru,terapis'])->group(function () {
    Route::resource('program-anak', App\Http\Controllers\ProgramAnakController::class)->only(['index', 'show']);
    Route::get('program-anak/program-konsultan/konsultan/{id}/list-json', [App\Http\Controllers\ProgramAnakController::class, 'listProgramKonsultan'])->name('program-anak.program-konsultan.list');
  });

  // PPI Routes - Program Pembelajaran Individual (admin, guru & konsultan)
  Route::middleware(['auth', 'role:admin,guru,konsultan'])->group(function () {
    Route::get('ppi', [App\Http\Controllers\PPIController::class, 'index'])->name('ppi.index');
    Route::get('ppi/create', [App\Http\Controllers\PPIController::class, 'create'])->name('ppi.create');
    Route::post('ppi', [App\Http\Controllers\PPIController::class, 'store'])->name('ppi.store');
    Route::get('ppi/{id}', [App\Http\Controllers\PPIController::class, 'show'])->name('ppi.show');
    Route::post('ppi/request-access', [App\Http\Controllers\GuruAnakDidikController::class, 'requestAccess'])->name('ppi.request-access');
    Route::get('ppi/riwayat/{id}', [App\Http\Controllers\PPIController::class, 'riwayat'])->name('ppi.riwayat');
    Route::get('ppi/{id}/detail-json', [App\Http\Controllers\PPIController::class, 'detailJson'])->name('ppi.detail.json');
    Route::post('ppi/{id}/approve', [App\Http\Controllers\PPIController::class, 'approve'])->name('ppi.approve');
    Route::put('ppi/{id}', [App\Http\Controllers\PPIController::class, 'update'])->name('ppi.update');
    Route::delete('ppi/{id}', [App\Http\Controllers\PPIController::class, 'destroy'])->name('ppi.destroy');
    // Approve/Reject access requests (for guru fokus)
    Route::get('guru-anak/approval-requests', [App\Http\Controllers\GuruAnakDidikController::class, 'approvalRequests'])->name('guru-anak.approvals.index')->middleware('role:admin');
    Route::post('guru-anak/approvals/{id}/approve', [App\Http\Controllers\GuruAnakDidikController::class, 'approveRequest'])->name('guru-anak.approvals.approve');
    Route::post('guru-anak/approvals/{id}/reject', [App\Http\Controllers\GuruAnakDidikController::class, 'rejectRequest'])->name('guru-anak.approvals.reject');
    Route::put('guru-anak/approvals/{id}', [App\Http\Controllers\GuruAnakDidikController::class, 'updateApproval'])->name('guru-anak.approvals.update');
    Route::delete('guru-anak/approvals/{id}', [App\Http\Controllers\GuruAnakDidikController::class, 'destroyApproval'])->name('guru-anak.approvals.destroy');
  });

  // Absensi: CRUD for admin, guru
  Route::middleware(['auth', 'role:admin,guru'])->group(function () {
    Route::resource('absensi', App\Http\Controllers\AbsensiController::class);
    Route::get('absensi/{id}/detail', [App\Http\Controllers\AbsensiController::class, 'showDetail'])->name('absensi.detail');
    Route::get('absensi/riwayat/{anakDidikId}', [App\Http\Controllers\AbsensiController::class, 'getRiwayatAbsensi'])->name('absensi.riwayat');
    Route::post('absensi/{id}/jemput', [App\Http\Controllers\AbsensiController::class, 'jemput'])->name('absensi.jemput');
  });

  // Absensi Export PDF (Admin only)
  Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('absensi-export-pdf', [App\Http\Controllers\AbsensiController::class, 'exportPdf'])->name('absensi.export-pdf');
  });

  // Admin-only endpoint for toggling PPI item active flag
  Route::middleware(['auth', 'role:admin'])->post('ppi/item/{id}/aktif', [App\Http\Controllers\PPIController::class, 'setItemAktif'])->name('ppi.item.aktif');

  // Notification routes for in-site notifications (pindahkan ke luar group agar tidak terbatasi role)
  Route::middleware(['auth'])->group(function () {
    Route::post('notifications/mark-read', [App\Http\Controllers\NotificationController::class, 'markRead'])->name('notifications.mark-read');
    Route::post('notifications/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
    Route::get('notifications/unread-json', [App\Http\Controllers\NotificationController::class, 'unreadJson'])->name('notifications.unread-json');
  });

  // Pasien Terapis
  // Index, jadwal, edit, update, destroy remain accessible to admin and terapis
  Route::middleware(['auth', 'role:admin,terapis'])->group(function () {
    Route::get('terapis/pasien', [App\Http\Controllers\TerapisPatientController::class, 'index'])->name('terapis.pasien.index');
    Route::get('terapis/pasien/{anakId}/jadwal', [App\Http\Controllers\TerapisPatientController::class, 'jadwalAnak'])->name('terapis.pasien.jadwal');
    Route::get('terapis/pasien/{id}/edit', [App\Http\Controllers\TerapisPatientController::class, 'edit'])->name('terapis.pasien.edit');
    Route::match(['put', 'patch'], 'terapis/pasien/{id}', [App\Http\Controllers\TerapisPatientController::class, 'update'])->name('terapis.pasien.update');
    Route::delete('terapis/pasien/{id}', [App\Http\Controllers\TerapisPatientController::class, 'destroy'])->name('terapis.pasien.destroy');
    Route::delete('terapis/jadwal/{id}', [App\Http\Controllers\TerapisPatientController::class, 'hapusJadwal'])->name('terapis.jadwal.destroy');
    Route::match(['put', 'patch'], 'terapis/jadwal/{id}', [App\Http\Controllers\TerapisPatientController::class, 'updateJadwal'])->name('terapis.jadwal.update');
  });

  // Create/store routes only for terapis (admin should not be able to create pasien terapis)
  Route::middleware(['auth', 'role:terapis'])->group(function () {
    Route::get('terapis/pasien/create', [App\Http\Controllers\TerapisPatientController::class, 'create'])->name('terapis.pasien.create');
    Route::post('terapis/pasien', [App\Http\Controllers\TerapisPatientController::class, 'store'])->name('terapis.pasien.store');
  });

  Route::post('/anak-didik/{id}/toggle-status', [
    App\Http\Controllers\AnakDidikController::class,
    'toggleStatus'
  ])->name('anak-didik.toggle-status');
});

// Tambahan route untuk API riwayat observasi/evaluasi anak didik di halaman program
require_once __DIR__ . '/program_riwayat.php';
