<?php

namespace App\Services;

use App\Models\Activity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityService
{
  /**
   * Log aktivitas ke database
   * 
   * @param string $action - create, update, delete, login, logout, approve, reject, dll
   * @param string $description - Deskripsi detail aktivitas
   * @param string|null $modelName - Nama model (AnakDidik, Konsultan, dll)
   * @param int|null $modelId - ID dari model
   */
  public static function log($action, $description, $modelName = null, $modelId = null)
  {
    try {
      $user = Auth::user();

      if (!$user) {
        return false;
      }

      Activity::create([
        'user_id' => $user->id,
        'action' => $action,
        'description' => $description,
        'model_name' => $modelName,
        'model_id' => $modelId,
        'ip_address' => Request::ip(),
        'user_agent' => Request::header('User-Agent'),
      ]);

      return true;
    } catch (\Exception $e) {
      // Log error jika diperlukan, tapi jangan break aplikasi
      \Log::error('ActivityService Error: ' . $e->getMessage());
      return false;
    }
  }

  /**
   * Log user login
   */
  public static function logLogin()
  {
    self::log('login', 'Login ke sistem');
  }

  /**
   * Log user logout
   */
  public static function logLogout()
  {
    self::log('logout', 'Logout dari sistem');
  }

  /**
   * Log create action
   */
  public static function logCreate($modelName, $modelId, $description = null)
  {
    $desc = $description ?? 'Membuat ' . self::getModelLabel($modelName);
    self::log('create', $desc, $modelName, $modelId);
  }

  /**
   * Log update action
   */
  public static function logUpdate($modelName, $modelId, $description = null)
  {
    $desc = $description ?? 'Mengupdate ' . self::getModelLabel($modelName);
    self::log('update', $desc, $modelName, $modelId);
  }

  /**
   * Log delete action
   */
  public static function logDelete($modelName, $modelId, $description = null)
  {
    $desc = $description ?? 'Menghapus ' . self::getModelLabel($modelName);
    self::log('delete', $desc, $modelName, $modelId);
  }

  /**
   * Log approve action
   */
  public static function logApprove($modelName, $modelId, $description = null)
  {
    $desc = $description ?? 'Menyetujui ' . self::getModelLabel($modelName);
    self::log('approve', $desc, $modelName, $modelId);
  }

  /**
   * Log reject action
   */
  public static function logReject($modelName, $modelId, $description = null)
  {
    $desc = $description ?? 'Menolak ' . self::getModelLabel($modelName);
    self::log('reject', $desc, $modelName, $modelId);
  }

  /**
   * Get readable model label
   */
  private static function getModelLabel($modelName)
  {
    $labels = [
      'AnakDidik' => 'Anak Didik',
      'Konsultan' => 'Konsultan',
      'Karyawan' => 'Karyawan',
      'Program' => 'Program',
      'TherapyProgram' => 'Program Terapi',
      'Assessment' => 'Penilaian',
      'User' => 'Pengguna',
    ];

    return $labels[$modelName] ?? $modelName;
  }
}
