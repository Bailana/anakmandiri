// resources/js/echo-pusher.js
// Inisialisasi Laravel Echo dengan Pusher untuk real-time notifikasi
console.log('echo-pusher.js loaded');

import Pusher from 'pusher-js';
window.Pusher = Pusher;
import Echo from 'laravel-echo';

if (typeof window.Pusher !== 'undefined') {
  window.Echo = new Echo({
    broadcaster: 'pusher',
    key: '0fa24aa4ebde54043934', // Ganti dengan PUSHER_APP_KEY Anda
    cluster: 'ap1', // Ganti dengan PUSHER_APP_CLUSTER Anda
    forceTLS: true
  });

  // Listener realtime notifikasi admin
  if (window.Echo) {
    console.log('Echo ready, listening on notifikasi-admin for .NotifikasiAksesPPI');
    window.Echo.channel('notifikasi-admin').listen('.NotifikasiAksesPPI', function (e) {
      console.log('Realtime event diterima', e);
      // Anda bisa update UI di sini, misal:
      // alert('Notifikasi baru: ' + e.message);
      if (window.CURRENT_USER_IS_ADMIN && window.updateNavbarNotification) {
        window.updateNavbarNotification(e, 'admin');
      }
    });

    // Debug log sebelum blok Guru
    console.log('Cek Guru:', window.CURRENT_USER_IS_GURU, window.CURRENT_USER_ID);
    // Listener realtime notifikasi guru
    if (window.CURRENT_USER_IS_GURU && window.CURRENT_USER_ID) {
      console.log('Masuk blok Guru listener');
      const guruChannel = 'notifikasi-guru-' + window.CURRENT_USER_ID;
      console.log('Echo ready, listening on ' + guruChannel + ' for .NotifikasiAksesPPI');
      window.Echo.channel(guruChannel).listen('.NotifikasiAksesPPI', function (e) {
        console.log('Realtime event diterima guru', e);
        if (window.updateNavbarNotification) {
          window.updateNavbarNotification(e, 'guru');
        }
      });
    }
  }
} else {
  console.error('Pusher is not defined!');
}
