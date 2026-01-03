<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LoginAttemptMail extends Mailable
{
  use Queueable, SerializesModels;

  public $name;
  public $resetUrl;
  public $time;

  /**
   * Create a new message instance.
   */
  public function __construct($name, $resetUrl = null, $time = null)
  {
    $this->name = $name;
    $this->resetUrl = $resetUrl;
    $this->time = $time;
  }

  /**
   * Build the message.
   */
  public function build()
  {
    return $this->subject('Peringatan: Percobaan Login Gagal')
      ->view('emails.login_attempt')
      ->with([
        'name' => $this->name,
        'resetUrl' => $this->resetUrl,
        'time' => $this->time,
      ]);
  }
}
