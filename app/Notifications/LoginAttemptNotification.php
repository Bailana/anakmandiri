<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class LoginAttemptNotification extends Notification
{
  use Queueable;

  protected $ip;
  protected $time;
  protected $token;
  protected $email;

  public function __construct($ip, $time, $token = null, $email = null)
  {
    $this->ip = $ip;
    $this->time = $time;
    $this->token = $token;
    $this->email = $email;
  }

  public function via($notifiable)
  {
    return ['mail'];
  }

  public function toMail($notifiable)
  {
    $data = [
      'name' => $notifiable->name ?? 'Pengguna',
      'time' => $this->time,
      'resetUrl' => null,
    ];

    if ($this->token && $this->email) {
      $data['resetUrl'] = route('password.reset', ['token' => $this->token, 'email' => $this->email]);
    }

    return (new MailMessage)
      ->subject('Peringatan: Percobaan Login Gagal')
      ->view('emails.login_attempt', $data);
  }
}
