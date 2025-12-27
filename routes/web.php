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
    return redirect()->route('dashboard-analytics');
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

  // Reset Password Routes
  Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
  Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::middleware(['auth'])->group(function () {
  Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

  // Profile Routes
  Route::get('/my-profile', [ProfileController::class, 'show'])->name('profile.show');
  Route::put('/my-profile', [ProfileController::class, 'update'])->name('profile.update');
  Route::put('/my-profile/password', [ProfileController::class, 'updatePassword'])->name('profile.updatePassword');
});

Route::get('/auth/register-basic', [RegisterBasic::class, 'index'])->name('auth-register-basic');
Route::get('/auth/forgot-password-basic', [AuthController::class, 'showForgotPassword'])->name('auth-reset-password-basic');

// Protected Routes
Route::middleware(['auth'])->group(function () {
  // Dashboard Routes - menggunakan satu route yang handle semua role
  Route::get('/dashboard', [Analytics::class, 'index'])->name('dashboard-analytics');

  // layout
  Route::get('/layouts/without-menu', [WithoutMenu::class, 'index'])->name('layouts-without-menu');
  Route::get('/layouts/without-navbar', [WithoutNavbar::class, 'index'])->name('layouts-without-navbar');
  Route::get('/layouts/fluid', [Fluid::class, 'index'])->name('layouts-fluid');
  Route::get('/layouts/container', [Container::class, 'index'])->name('layouts-container');
  Route::get('/layouts/blank', [Blank::class, 'index'])->name('layouts-blank');

  // pages
  Route::get('/pages/account-settings-account', [AccountSettingsAccount::class, 'index'])->name('pages-account-settings-account');
  Route::get('/pages/account-settings-notifications', [AccountSettingsNotifications::class, 'index'])->name('pages-account-settings-notifications');
  Route::get('/pages/account-settings-connections', [AccountSettingsConnections::class, 'index'])->name('pages-account-settings-connections');
  Route::get('/pages/misc-error', [MiscError::class, 'index'])->name('pages-misc-error');
  Route::get('/pages/misc-under-maintenance', [MiscUnderMaintenance::class, 'index'])->name('pages-misc-under-maintenance');

  // cards
  Route::get('/cards/basic', [CardBasic::class, 'index'])->name('cards-basic');

  // User Interface
  Route::get('/ui/accordion', [Accordion::class, 'index'])->name('ui-accordion');
  Route::get('/ui/alerts', [Alerts::class, 'index'])->name('ui-alerts');
  Route::get('/ui/badges', [Badges::class, 'index'])->name('ui-badges');
  Route::get('/ui/buttons', [Buttons::class, 'index'])->name('ui-buttons');
  Route::get('/ui/carousel', [Carousel::class, 'index'])->name('ui-carousel');
  Route::get('/ui/collapse', [Collapse::class, 'index'])->name('ui-collapse');
  Route::get('/ui/dropdowns', [Dropdowns::class, 'index'])->name('ui-dropdowns');
  Route::get('/ui/footer', [Footer::class, 'index'])->name('ui-footer');
  Route::get('/ui/list-groups', [ListGroups::class, 'index'])->name('ui-list-groups');
  Route::get('/ui/modals', [Modals::class, 'index'])->name('ui-modals');
  Route::get('/ui/navbar', [Navbar::class, 'index'])->name('ui-navbar');
  Route::get('/ui/offcanvas', [Offcanvas::class, 'index'])->name('ui-offcanvas');
  Route::get('/ui/pagination-breadcrumbs', [PaginationBreadcrumbs::class, 'index'])->name('ui-pagination-breadcrumbs');
  Route::get('/ui/progress', [Progress::class, 'index'])->name('ui-progress');
  Route::get('/ui/spinners', [Spinners::class, 'index'])->name('ui-spinners');
  Route::get('/ui/tabs-pills', [TabsPills::class, 'index'])->name('ui-tabs-pills');
  Route::get('/ui/toasts', [Toasts::class, 'index'])->name('ui-toasts');
  Route::get('/ui/tooltips-popovers', [TooltipsPopovers::class, 'index'])->name('ui-typography');

  // extended ui
  Route::get('/extended/ui-perfect-scrollbar', [PerfectScrollbar::class, 'index'])->name('extended-ui-perfect-scrollbar');
  Route::get('/extended/ui-text-divider', [TextDivider::class, 'index'])->name('extended-ui-text-divider');

  // icons
  Route::get('/icons/icons-ri', [RiIcons::class, 'index'])->name('icons-ri');

  // form elements
  Route::get('/forms/basic-inputs', [BasicInput::class, 'index'])->name('forms-basic-inputs');
  Route::get('/forms/input-groups', [InputGroups::class, 'index'])->name('forms-input-groups');

  // form layouts
  Route::get('/form/layouts-vertical', [VerticalForm::class, 'index'])->name('form-layouts-vertical');
  Route::get('/form/layouts-horizontal', [HorizontalForm::class, 'index'])->name('form-layouts-horizontal');

  // tables
  Route::get('/tables/basic', [TablesBasic::class, 'index'])->name('tables-basic');

  // Admin Only Routes
  // Anak Didik Routes (admin & guru & konsultan: index/show, admin: full)
  // Semua user bisa akses daftar & detail anak didik

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
  });
  // Allow konsultan, terapis, admin, and guru to view program index/show (read-only for guru)
  Route::middleware(['auth', 'role:admin,konsultan,terapis,guru'])->group(function () {
    Route::resource('program', 'App\Http\Controllers\ProgramController')->only(['index', 'show']);
  });
  Route::middleware(['auth', 'role:konsultan'])->group(function () {
    Route::resource('program', 'App\Http\Controllers\ProgramController')->only(['create', 'store']);
  });

  // Assessment: admin & guru (guru hanya index/show)
  Route::middleware(['auth', 'role:admin,guru'])->group(function () {
    Route::get('assessment/ppi-programs', [App\Http\Controllers\AssessmentController::class, 'ppiPrograms'])->name('assessment.ppi-programs');
    // Program history per anak (used for small charts on index)
    Route::get('assessment/{anakId}/program-history', [App\Http\Controllers\AssessmentController::class, 'programHistory'])->name('assessment.program-history');
    Route::resource('assessment', 'App\\Http\\Controllers\\AssessmentController');
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
    // Notification routes for in-site notifications
    Route::post('notifications/mark-read', [App\Http\Controllers\NotificationController::class, 'markRead'])->name('notifications.mark-read');
    Route::post('notifications/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
    Route::get('notifications/unread-json', [App\Http\Controllers\NotificationController::class, 'unreadJson'])->name('notifications.unread-json');
    // Approve/Reject access requests (for guru fokus)
    Route::get('guru-anak/approval-requests', [App\Http\Controllers\GuruAnakDidikController::class, 'approvalRequests'])->name('guru-anak.approvals.index')->middleware('role:admin');
    Route::post('guru-anak/approvals/{id}/approve', [App\Http\Controllers\GuruAnakDidikController::class, 'approveRequest'])->name('guru-anak.approvals.approve');
    Route::post('guru-anak/approvals/{id}/reject', [App\Http\Controllers\GuruAnakDidikController::class, 'rejectRequest'])->name('guru-anak.approvals.reject');
    Route::put('guru-anak/approvals/{id}', [App\Http\Controllers\GuruAnakDidikController::class, 'updateApproval'])->name('guru-anak.approvals.update');
    Route::delete('guru-anak/approvals/{id}', [App\Http\Controllers\GuruAnakDidikController::class, 'destroyApproval'])->name('guru-anak.approvals.destroy');
  });

  // Pasien Terapis - accessible to admin and terapis
  Route::middleware(['auth', 'role:admin,terapis'])->group(function () {
    Route::get('terapis/pasien', [App\Http\Controllers\TerapisPatientController::class, 'index'])->name('terapis.pasien.index');
    Route::get('terapis/pasien/create', [App\Http\Controllers\TerapisPatientController::class, 'create'])->name('terapis.pasien.create');
    Route::post('terapis/pasien', [App\Http\Controllers\TerapisPatientController::class, 'store'])->name('terapis.pasien.store');
    Route::get('terapis/pasien/{anakId}/jadwal', [App\Http\Controllers\TerapisPatientController::class, 'jadwalAnak'])->name('terapis.pasien.jadwal');
    Route::get('terapis/pasien/{id}/edit', [App\Http\Controllers\TerapisPatientController::class, 'edit'])->name('terapis.pasien.edit');
    Route::match(['put', 'patch'], 'terapis/pasien/{id}', [App\Http\Controllers\TerapisPatientController::class, 'update'])->name('terapis.pasien.update');
    Route::delete('terapis/pasien/{id}', [App\Http\Controllers\TerapisPatientController::class, 'destroy'])->name('terapis.pasien.destroy');
    Route::delete('terapis/jadwal/{id}', [App\Http\Controllers\TerapisPatientController::class, 'hapusJadwal'])->name('terapis.jadwal.destroy');
    Route::match(['put', 'patch'], 'terapis/jadwal/{id}', [App\Http\Controllers\TerapisPatientController::class, 'updateJadwal'])->name('terapis.jadwal.update');
  });
});

// Tambahan route untuk API riwayat observasi/evaluasi anak didik di halaman program
require_once __DIR__ . '/program_riwayat.php';
