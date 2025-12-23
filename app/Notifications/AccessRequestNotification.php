<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class AccessRequestNotification extends Notification
{
  use Queueable;

  protected $actorId;
  protected $actorName;
  protected $anakId;
  protected $targetName;
  protected $approvalId;
  protected $action;
  protected $url;

  public function __construct($actorId, $actorName, $anakId, $targetName = '', $approvalId = null, $action = 'requested', $url = null)
  {
    $this->actorId = $actorId;
    $this->actorName = $actorName;
    $this->anakId = $anakId;
    $this->targetName = $targetName;
    $this->approvalId = $approvalId;
    $this->action = $action;
    $this->url = $url;
  }

  public function via($notifiable)
  {
    return ['database'];
  }

  public function toDatabase($notifiable)
  {
    $action = $this->action ?? 'requested';
    $data = [
      'type' => 'access_request',
      'action' => $action,
      'actor_user_id' => $this->actorId,
      'actor_name' => $this->actorName,
      'anak_didik_id' => $this->anakId,
      'target_name' => $this->targetName,
      'approval_id' => $this->approvalId,
    ];

    // default URLs
    $defaultUrl = url('/guru-anak/approval-requests');
    if ($action === 'approved') {
      $data['message'] = sprintf('%s telah menyetujui permintaan akses Anda untuk %s', $this->actorName, $this->targetName ?: 'anak ini');
      $data['url'] = $this->url ?? url('/ppi');
    } elseif ($action === 'rejected') {
      $data['message'] = sprintf('%s menolak permintaan akses Anda untuk %s', $this->actorName, $this->targetName ?: 'anak ini');
      $data['url'] = $this->url ?? $defaultUrl;
    } else {
      $data['message'] = sprintf('%s meminta akses melihat riwayat PPI untuk %s', $this->actorName, $this->targetName ?: 'anak ini');
      $data['url'] = $this->url ?? $defaultUrl;
    }

    return $data;
  }
}
