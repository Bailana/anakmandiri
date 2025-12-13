<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Activity extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_id',
    'action',
    'description',
    'model_name',
    'model_id',
    'ip_address',
    'user_agent',
  ];

  protected $casts = [
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
  ];

  /**
   * Relationship dengan User
   */
  public function user()
  {
    return $this->belongsTo(User::class);
  }

  /**
   * Scope untuk aktivitas terbaru
   */
  public function scopeLatest($query, $limit = 10)
  {
    return $query->orderBy('created_at', 'desc')->limit($limit);
  }

  /**
   * Get activity label berdasarkan action
   */
  public function getActivityLabel()
  {
    $labels = [
      'login' => 'Login ke sistem',
      'logout' => 'Logout dari sistem',
      'create' => 'Membuat ' . strtolower($this->model_name),
      'update' => 'Mengupdate ' . strtolower($this->model_name),
      'delete' => 'Menghapus ' . strtolower($this->model_name),
      'approve' => 'Menyetujui ' . strtolower($this->model_name),
      'reject' => 'Menolak ' . strtolower($this->model_name),
      'upload' => 'Upload file',
      'download' => 'Download file',
    ];

    return $labels[$this->action] ?? $this->description;
  }

  /**
   * Get status badge color
   */
  public function getStatusColor()
  {
    return 'success'; // Bisa disesuaikan jika ada status field
  }
}
