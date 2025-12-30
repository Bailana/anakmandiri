<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\NotifikasiBaru;

class BroadcastTestController extends Controller
{
  public function kirimNotifikasi(Request $request)
  {
    $pesan = $request->input('pesan', 'Ada notifikasi baru!');
    event(new NotifikasiBaru($pesan));
    return response()->json(['status' => 'Notifikasi dikirim', 'pesan' => $pesan]);
  }
}
