<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuruAnakDidikApproval extends Model
{
  protected $table = 'guru_anak_didik_approvals';

  protected $fillable = [
    'requester_user_id',
    'approver_user_id',
    'anak_didik_id',
    'status',
    'reason',
    'approval_notes',
    'approved_at',
  ];

  protected $casts = [
    'approved_at' => 'datetime',
  ];

  public function requesterUser()
  {
    return $this->belongsTo(User::class, 'requester_user_id');
  }

  public function approverUser()
  {
    return $this->belongsTo(User::class, 'approver_user_id');
  }

  public function anakDidik()
  {
    return $this->belongsTo(AnakDidik::class);
  }
}
