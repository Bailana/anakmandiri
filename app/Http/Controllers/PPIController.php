<?php

namespace App\Http\Controllers;

use App\Models\AnakDidik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\GuruAnakDidikController;

class PPIController extends Controller
{
  public function index(Request $request)
  {
    $user = Auth::user();

    $query = AnakDidik::with('guruFokus')->orderBy('nama');
    if ($request->filled('search')) {
      $q = $request->search;
      $query->where(function ($s) use ($q) {
        $s->where('nama', 'like', "%{$q}%")->orWhere('nis', 'like', "%{$q}%");
      });
    }

    $perPage = 15;
    $anakPaginator = $query->paginate($perPage)->appends($request->query());

    // Build access map for items on current page only
    $accessMap = [];
    // Try to resolve current user to a Karyawan record (match by name) to cover guru_fokus mapping
    $karyawanForUser = null;
    if ($user && $user->role === 'guru') {
      $karyawanForUser = \App\Models\Karyawan::where('nama', $user->name)->first();
    }
    foreach ($anakPaginator->items() as $anak) {
      if ($user->role === 'admin') {
        $accessMap[$anak->id] = true;
        continue;
      }
      // Direct assignment or approved request
      $can = GuruAnakDidikController::canAccessChild($user->id, $anak->id);
      // Additionally, if the anak's guruFokus (Karyawan) matches this user's Karyawan record, grant access
      if (!$can && $karyawanForUser && $anak->guru_fokus_id && $karyawanForUser->id == $anak->guru_fokus_id) {
        $can = true;
      }
      $accessMap[$anak->id] = $can;
    }

    return view('content.ppi.index', [
      'anakList' => $anakPaginator,
      'accessMap' => $accessMap,
      'search' => $request->search ?? null,
    ]);
  }

  public function show($id)
  {
    $user = Auth::user();
    $anak = AnakDidik::with('guruFokus')->findOrFail($id);

    $canAccess = false;
    if ($user->role === 'admin') {
      $canAccess = true;
    } else {
      $canAccess = GuruAnakDidikController::canAccessChild($user->id, $anak->id);
      if (!$canAccess && $user->role === 'guru') {
        $karyawan = \App\Models\Karyawan::where('nama', $user->name)->first();
        if ($karyawan && $anak->guru_fokus_id && $karyawan->id == $anak->guru_fokus_id) {
          $canAccess = true;
        }
      }
    }

    if (!$canAccess) {
      return view('content.ppi.locked', ['anak' => $anak]);
    }

    // If allowed, show program anak detail page (reuse program-anak.show if exists)
    return redirect()->route('program-anak.show', $anak->id);
  }
}
