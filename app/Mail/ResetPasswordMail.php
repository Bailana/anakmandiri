<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
  use Queueable, SerializesModels;

  public $name;
  public $resetUrl;
  public $time;
  public $lead;
  public $buttonText;

  public function __construct($name, $resetUrl = null, $time = null)
  {
    $this->name = $name;
    $this->resetUrl = $resetUrl;
    $this->time = $time;
    $this->lead = 'Silakan klik tombol di bawah untuk mereset password Anda.';
    $this->buttonText = 'Reset Password';
  }

  public function build()
  {
    return $this->subject('Reset Password')
      ->view('emails.login_attempt')
      ->with([
        'name' => $this->name,
        'resetUrl' => $this->resetUrl,
        'time' => $this->time,
        'lead' => $this->lead,
        'buttonText' => $this->buttonText,
      ]);
  }
}
