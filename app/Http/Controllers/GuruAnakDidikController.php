<?php

namespace App\Http\Controllers;

use App\Models\GuruAnakDidik;
use App\Models\GuruAnakDidikApproval;
use App\Models\AnakDidik;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuruAnakDidikController extends Controller
{
  /**
   * Display list of anak that are assigned to guru
   */
  public function myChildren(Request $request)
  {
    $user = Auth::user();

    $query = GuruAnakDidik::where('user_id', $user->id)
      ->with('anakDidik')
      ->where('status', 'aktif');

    if ($request->filled('search')) {
      $search = $request->search;
      $query->whereHas('anakDidik', function ($q) use ($search) {
        $q->where('nama', 'like', "%{$search}%")
          ->orWhere('nis', 'like', "%{$search}%");
      });
    }

    $myChildren = $query->paginate(10)->appends($request->query());

    return view('content.guru-anak.my-children', ['myChildren' => $myChildren]);
  }

  /**
   * Assign new anak didik to guru (admin only)
   */
  public function assign(Request $request)
  {
    // Check if guru already has 3 children
    $guru = User::findOrFail($request->guru_id);
    $currentCount = GuruAnakDidik::where('user_id', $guru->id)
      ->where('status', 'aktif')
      ->count();

    if ($currentCount >= 3) {
      return response()->json([
        'success' => false,
        'message' => 'Guru sudah memiliki 3 anak didik yang aktif (maksimal)',
      ], 422);
    }

    $validated = $request->validate([
      'guru_id' => 'required|exists:users,id',
      'anak_didik_id' => 'required|exists:anak_didiks,id',
    ]);

    // Check if assignment already exists
    $existing = GuruAnakDidik::where('user_id', $validated['guru_id'])
      ->where('anak_didik_id', $validated['anak_didik_id'])
      ->first();

    if ($existing) {
      return response()->json([
        'success' => false,
        'message' => 'Guru sudah ditugaskan ke anak didik ini',
      ], 422);
    }

    GuruAnakDidik::create([
      'user_id' => $validated['guru_id'],
      'anak_didik_id' => $validated['anak_didik_id'],
      'status' => 'aktif',
      'tanggal_mulai' => now(),
    ]);

    return response()->json([
      'success' => true,
      'message' => 'Penugasan anak didik berhasil',
    ]);
  }

  /**
   * Request access to another child
   */
  public function requestAccess(Request $request)
  {
    $validated = $request->validate([
      'anak_didik_id' => 'required|exists:anak_didiks,id',
      'reason' => 'nullable|string|max:500',
    ]);

    $user = Auth::user();
    $anakDidik = AnakDidik::findOrFail($validated['anak_didik_id']);

    // Find the guru fokus (who has active assignment to this child)
    $guruFokus = GuruAnakDidik::where('anak_didik_id', $validated['anak_didik_id'])
      ->where('status', 'aktif')
      ->first();

    if (!$guruFokus) {
      return response()->json([
        'success' => false,
        'message' => 'Guru fokus untuk anak ini tidak ditemukan',
      ], 404);
    }

    // Check if already has pending request
    $pending = GuruAnakDidikApproval::where('requester_user_id', $user->id)
      ->where('approver_user_id', $guruFokus->user_id)
      ->where('anak_didik_id', $validated['anak_didik_id'])
      ->where('status', 'pending')
      ->exists();

    if ($pending) {
      return response()->json([
        'success' => false,
        'message' => 'Anda sudah memiliki permintaan akses yang pending untuk anak ini',
      ], 422);
    }

    GuruAnakDidikApproval::create([
      'requester_user_id' => $user->id,
      'approver_user_id' => $guruFokus->user_id,
      'anak_didik_id' => $validated['anak_didik_id'],
      'status' => 'pending',
      'reason' => $validated['reason'] ?? null,
    ]);

    return response()->json([
      'success' => true,
      'message' => 'Permintaan akses telah dikirim ke guru fokus',
    ]);
  }

  /**
   * View pending approval requests (for guru fokus)
   */
  public function approvalRequests(Request $request)
  {
    $user = Auth::user();

    $query = GuruAnakDidikApproval::where('approver_user_id', $user->id)
      ->with(['requesterUser', 'anakDidik']);

    if ($request->filled('status')) {
      $query->where('status', $request->status);
    }

    $requests = $query->paginate(10)->appends($request->query());

    return view('content.guru-anak.approval-requests', ['requests' => $requests]);
  }

  /**
   * Approve access request
   */
  public function approveRequest(string $id)
  {
    $approval = GuruAnakDidikApproval::findOrFail($id);

    if (Auth::id() !== $approval->approver_user_id) {
      return response()->json([
        'success' => false,
        'message' => 'Anda tidak berhak mengakses request ini',
      ], 403);
    }

    $approval->update([
      'status' => 'approved',
      'approved_at' => now(),
    ]);

    return response()->json([
      'success' => true,
      'message' => 'Permintaan akses telah disetujui',
    ]);
  }

  /**
   * Reject access request
   */
  public function rejectRequest(string $id, Request $request)
  {
    $approval = GuruAnakDidikApproval::findOrFail($id);

    if (Auth::id() !== $approval->approver_user_id) {
      return response()->json([
        'success' => false,
        'message' => 'Anda tidak berhak mengakses request ini',
      ], 403);
    }

    $validated = $request->validate([
      'approval_notes' => 'nullable|string|max:500',
    ]);

    $approval->update([
      'status' => 'rejected',
      'approval_notes' => $validated['approval_notes'] ?? null,
    ]);

    return response()->json([
      'success' => true,
      'message' => 'Permintaan akses telah ditolak',
    ]);
  }

  /**
   * Check if user can access specific child
   */
  public static function canAccessChild($userId, $anakDidikId)
  {
    // Check if guru is directly assigned
    $direct = GuruAnakDidik::where('user_id', $userId)
      ->where('anak_didik_id', $anakDidikId)
      ->where('status', 'aktif')
      ->exists();

    if ($direct) {
      return true;
    }

    // Check if has approved access request
    $approved = GuruAnakDidikApproval::where('requester_user_id', $userId)
      ->where('anak_didik_id', $anakDidikId)
      ->where('status', 'approved')
      ->exists();

    return $approved;
  }
}
