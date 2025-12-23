<?php

namespace App\Http\Controllers;

use App\Models\GuruAnakDidik;
use App\Models\GuruAnakDidikApproval;
use App\Models\AnakDidik;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AccessRequestNotification;

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

    // If no guru fokus is found, we will still create a pending approval
    // record with a null approver_user_id so admins can review/assign later.
    $approverUserId = $guruFokus ? $guruFokus->user_id : null;

    // Check if already has pending request
    $pendingQuery = GuruAnakDidikApproval::where('requester_user_id', $user->id)
      ->where('anak_didik_id', $validated['anak_didik_id'])
      ->where('status', 'pending');

    // match approver_user_id if we have one, otherwise check for pending requests without approver
    if ($approverUserId) {
      $pendingQuery->where('approver_user_id', $approverUserId);
    } else {
      $pendingQuery->whereNull('approver_user_id');
    }

    $pending = $pendingQuery->exists();

    if ($pending) {
      return response()->json([
        'success' => false,
        'message' => 'Anda sudah memiliki permintaan akses yang pending untuk anak ini',
      ], 422);
    }

    $approval = GuruAnakDidikApproval::create([
      'requester_user_id' => $user->id,
      'approver_user_id' => $approverUserId,
      'anak_didik_id' => $validated['anak_didik_id'],
      'status' => 'pending',
      'reason' => $validated['reason'] ?? null,
    ]);

    // Send in-site (database) notification to the approver if present, otherwise to admins
    try {
      $anakName = $anakDidik->nama ?? '';
      $requesterName = $user->name ?? '';
      // If approver exists but is the same as requester, notify admins only (don't notify self)
      if ($approverUserId && $approverUserId == $user->id) {
        $admins = User::where('role', 'admin')->get();
        if ($admins && $admins->count()) {
          Notification::send($admins, new AccessRequestNotification($user->id, $requesterName, $anakDidik->id, $anakName, $approval->id));
        }
      } elseif ($approverUserId) {
        // notify the approver (guru fokus)
        $approver = User::find($approverUserId);
        if ($approver) {
          // skip notifying konsultan or terapis accounts
          if (!in_array($approver->role, ['konsultan', 'terapis'])) {
            $approver->notify(new AccessRequestNotification($user->id, $requesterName, $anakDidik->id, $anakName, $approval->id));
          }
        }
      } else {
        // no approver configured -> notify admins
        $admins = User::where('role', 'admin')->get();
        if ($admins && $admins->count()) {
          Notification::send($admins, new AccessRequestNotification($user->id, $requesterName, $anakDidik->id, $anakName, $approval->id));
        }
      }
    } catch (\Throwable $ex) {
      // don't block creation if notification fails; log if desired
    }

    return response()->json([
      'success' => true,
      'message' => $approverUserId ? 'Permintaan akses telah dikirim ke guru fokus' : 'Permintaan akses telah disimpan; admin akan meninjau',
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

    $user = Auth::user();
    // Allow if user is admin or the configured approver
    if (!($user && ($user->role === 'admin' || Auth::id() === $approval->approver_user_id))) {
      return response()->json([
        'success' => false,
        'message' => 'Anda tidak berhak mengakses request ini',
      ], 403);
    }

    // If approver_user_id is null (admin acting), set it to current admin for audit
    if (!$approval->approver_user_id) {
      $approval->approver_user_id = $user->id;
    }

    $approval->status = 'approved';
    $approval->approved_at = now();
    $approval->save();

    // prepare requester info for response and send database notification
    $requesterName = null;
    try {
      $requesterId = $approval->requester_user_id;
      $anakId = $approval->anak_didik_id;
      $requester = User::find($requesterId);
      if ($requester) {
        $requesterName = $requester->name ?? null;
        // do not notify konsultan or terapis accounts
        if (!in_array($requester->role, ['konsultan', 'terapis'])) {
          $requester->notify(new \App\Notifications\AccessRequestNotification($user->id, $user->name ?? 'Admin', $anakId, $requester->name ?? '', $approval->id, 'approved'));
        }
      }
    } catch (\Throwable $ex) {
      // swallow notification errors
    }

    return response()->json([
      'success' => true,
      'message' => 'Permintaan akses telah disetujui',
      'requester_name' => $requesterName,
    ]);
  }

  /**
   * Reject access request
   */
  public function rejectRequest(string $id, Request $request)
  {
    $approval = GuruAnakDidikApproval::findOrFail($id);

    $user = Auth::user();
    // Allow if user is admin or the configured approver
    if (!($user && ($user->role === 'admin' || Auth::id() === $approval->approver_user_id))) {
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

    // notify requester about rejection (optional)
    $requesterName = null;
    try {
      $requester = User::find($approval->requester_user_id);
      if ($requester) {
        $requesterName = $requester->name ?? null;
        // do not notify konsultan or terapis accounts
        if (!in_array($requester->role, ['konsultan', 'terapis'])) {
          $requester->notify(new \App\Notifications\AccessRequestNotification($user->id, $user->name ?? 'Admin', $approval->anak_didik_id, $requester->name ?? '', $approval->id, 'rejected'));
        }
      }
    } catch (\Throwable $ex) {
      // ignore
    }

    return response()->json([
      'success' => true,
      'message' => 'Permintaan akses telah ditolak',
      'requester_name' => $requesterName,
    ]);
  }

  /**
   * Update approval (edit reason)
   */
  public function updateApproval(Request $request, string $id)
  {
    $approval = GuruAnakDidikApproval::findOrFail($id);
    $user = Auth::user();
    if (!($user && ($user->role === 'admin' || Auth::id() === $approval->approver_user_id))) {
      return response()->json(['success' => false, 'message' => 'Anda tidak berhak mengubah request ini'], 403);
    }

    $validated = $request->validate([
      'reason' => 'nullable|string|max:500',
    ]);

    $approval->reason = $validated['reason'] ?? $approval->reason;
    $approval->save();

    return response()->json(['success' => true, 'message' => 'Permintaan berhasil diperbarui']);
  }

  /**
   * Delete approval request
   */
  public function destroyApproval(string $id)
  {
    $approval = GuruAnakDidikApproval::findOrFail($id);
    $user = Auth::user();
    // only admin or approver can delete
    if (!($user && ($user->role === 'admin' || Auth::id() === $approval->approver_user_id))) {
      return response()->json(['success' => false, 'message' => 'Anda tidak berhak menghapus request ini'], 403);
    }

    try {
      $approval->delete();
      return response()->json(['success' => true, 'message' => 'Permintaan akses berhasil dihapus']);
    } catch (\Throwable $ex) {
      return response()->json(['success' => false, 'message' => 'Gagal menghapus permintaan'], 500);
    }
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
    // Only consider approvals that were approved within the last 600 minutes
    $approved = GuruAnakDidikApproval::where('requester_user_id', $userId)
      ->where('anak_didik_id', $anakDidikId)
      ->where('status', 'approved')
      ->whereNotNull('approved_at')
      ->where('approved_at', '>=', now()->subMinutes(600))
      ->exists();

    return $approved;
  }
}
