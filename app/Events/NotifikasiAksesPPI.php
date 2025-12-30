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

  /**
   * Create a new event instance.
   */
  public function __construct($guruId, $guruNama, $message, $id)
  {
    $this->guruId = $guruId;
    $this->guruNama = $guruNama;
    $this->message = $message;
    $this->id = $id;
  }

  /**
   * Get the channels the event should broadcast on.
   */
  public function broadcastOn()
  {
    // Broadcast ke channel admin dan channel guru terkait
    return [
      new Channel('notifikasi-admin'),
      new Channel('notifikasi-guru-' . $this->guruId)
    ];
  }

  public function broadcastAs()
  {
    return 'NotifikasiAksesPPI';
  }
}
