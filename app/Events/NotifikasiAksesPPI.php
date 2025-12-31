<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotifikasiAksesPPI implements ShouldBroadcastNow
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  public $guruId;
  public $guruNama;
  public $message;
  public $id;
  public $approval_id;
  public $target; // 'admin' or 'guru'

  /**
   * Create a new event instance.
   */
  public function __construct($guruId, $guruNama, $message, $id, $approval_id, $target = 'admin')
  {
    $this->guruId = $guruId;
    $this->guruNama = $guruNama;
    $this->message = $message;
    $this->id = $id;
    $this->approval_id = $approval_id;
    $this->target = $target; // 'admin' or 'guru'
  }

  /**
   * Get the channels the event should broadcast on.
   */
  public function broadcastOn()
  {
    // Hanya broadcast ke channel sesuai target
    if ($this->target === 'guru') {
      return [new Channel('notifikasi-guru-' . $this->guruId)];
    } else {
      return [new Channel('notifikasi-admin')];
    }
  }

  public function broadcastAs()
  {
    return 'NotifikasiAksesPPI';
  }

  /**
   * Data yang dikirim ke frontend via broadcast
   */
  public function broadcastWith()
  {
    // Ambil status approval dari DB jika approval_id ada
    $status = null;
    if ($this->approval_id) {
      $approval = \App\Models\GuruAnakDidikApproval::find($this->approval_id);
      if ($approval) {
        $status = $approval->status;
      }
    }
    return [
      'id' => $this->id,
      'guruId' => $this->guruId,
      'guruNama' => $this->guruNama,
      'message' => $this->message,
      'approval_id' => $this->approval_id,
      'is_admin' => ($this->target === 'admin'),
      'status' => $status
    ];
  }
}
