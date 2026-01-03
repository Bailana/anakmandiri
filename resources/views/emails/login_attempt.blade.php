<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
</head>

<body style="margin:0;padding:0;background-color:#f5f7fb;font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;color:#374151;">

  <!-- Wrapper table -->
  <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#f5f7fb;padding:40px 0;width:100%;">
    <tr>
      <td align="center">
        <!-- Card table -->
        <table width="600" cellpadding="0" cellspacing="0" role="presentation" style="width:600px;max-width:600px;background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 10px rgba(16,24,40,0.08);">
          <tr>
            <td style="padding:22px 28px 12px 28px;text-align:center;vertical-align:middle;">
              @if(isset($message))
              <img src="{{ $message->embed(public_path('assets/img/am.png')) }}" alt="{{ config('app.name', 'Aplikasi') }}" width="90" height="90" style="display:block;border:0;outline:none;text-decoration:none;margin:0 auto;">
              @else
              <img src="{{ asset('assets/img/am.png') }}" alt="{{ config('app.name', 'Aplikasi') }}" width="90" height="90" style="display:block;border:0;outline:none;text-decoration:none;margin:0 auto;">
              @endif
            </td>
          </tr>

          <tr>
            <td style="padding:28px;">
              <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width:520px;margin:0 auto;">
                <tr>
                  <td style="font-size:16px;color:#0f172a;padding-bottom:8px;">
                    Halo {{ $name ?? 'Pengguna' }},
                  </td>
                </tr>
                <tr>
                  <td style="font-size:15px;color:#475569;line-height:1.5;padding-bottom:18px;">
                    {{ $lead ?? 'Kami mendeteksi beberapa percobaan login yang gagal ke akun Anda. Jika ini bukan Anda, segera ganti password Anda dan periksa aktivitas akun.' }}
                  </td>
                </tr>

                @if(!empty($resetUrl))
                <tr>
                  <td align="center" style="padding:12px 0 18px 0;">
                    <!-- Button (table-based for better compatibility) -->
                    <table cellpadding="0" cellspacing="0" role="presentation" style="margin:0 auto;">
                      <tr>
                        <td align="center" bgcolor="#ef4444" style="border-radius:6px;">
                          <a href="{{ $resetUrl }}" target="_blank" rel="noopener" style="display:inline-block;padding:12px 22px;font-weight:600;color:#ffffff;text-decoration:none;border-radius:6px;">{{ $buttonText ?? 'Reset Password' }}</a>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
                @endif

                <tr>
                  <td style="padding-top:8px;padding-bottom:6px;color:#475569;">
                    Terima kasih,
                  </td>
                </tr>
                <tr>
                  <td style="font-weight:700;color:#0f172a;padding-bottom:4px;">
                    Tim IT Klinik Terapi & Sekolah Khusus Anak Mandiri
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <tr>
            <td style="background:#fbfdff;padding:16px 28px;font-size:13px;color:#64748b;">
              Jika Anda tidak meminta ini, Anda dapat mengabaikan email ini.
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

</body>

</html>