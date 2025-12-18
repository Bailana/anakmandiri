<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Activity;
use App\Models\User;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
  public function index(Request $request)
  {
    $query = Activity::with('user')->orderBy('created_at', 'desc');

    // Search keyword: search in user name, description, action, model_name
    if ($request->filled('search')) {
      $kw = $request->search;
      $query->where(function ($q) use ($kw) {
        $q->whereHas('user', function ($q2) use ($kw) {
          $q2->where('name', 'like', "%{$kw}%");
        })
          ->orWhere('description', 'like', "%{$kw}%")
          ->orWhere('action', 'like', "%{$kw}%")
          ->orWhere('model_name', 'like', "%{$kw}%");
      });
    }

    // Filter by role
    if ($request->filled('role') && $request->role !== 'all') {
      $role = $request->role;
      $query->whereHas('user', function ($q) use ($role) {
        $q->where('role', $role);
      });
    }

    // Date range filter: support presets via `range` or custom from/to
    // range: all|today|7|30|custom
    $range = $request->get('range', 'all');
    if ($range !== 'all') {
      if ($range === 'today') {
        $from = Carbon::today()->startOfDay();
        $to = Carbon::today()->endOfDay();
      } elseif ($range === '7') {
        $from = Carbon::today()->subDays(6)->startOfDay();
        $to = Carbon::today()->endOfDay();
      } elseif ($range === '30') {
        $from = Carbon::today()->subDays(29)->startOfDay();
        $to = Carbon::today()->endOfDay();
      } elseif ($range === 'custom' && $request->filled('from') && $request->filled('to')) {
        $from = Carbon::createFromFormat('Y-m-d', $request->from)->startOfDay();
        $to = Carbon::createFromFormat('Y-m-d', $request->to)->endOfDay();
      } else {
        $from = null;
        $to = null;
      }

      if ($from && $to) {
        $query->whereBetween('created_at', [$from, $to]);
      }
    }

    $activities = $query->paginate(10)->appends($request->except('page'));

    $roles = User::select('role')->distinct()->pluck('role')->toArray();

    return view('content.activity.logs', compact('activities', 'roles'));
  }

  /**
   * Export activities CSV based on same filters
   */
  public function export(Request $request)
  {
    $query = Activity::with('user')->orderBy('created_at', 'desc');

    // apply same filters as index
    if ($request->filled('search')) {
      $kw = $request->search;
      $query->where(function ($q) use ($kw) {
        $q->whereHas('user', function ($q2) use ($kw) {
          $q2->where('name', 'like', "%{$kw}%");
        })
          ->orWhere('description', 'like', "%{$kw}%")
          ->orWhere('action', 'like', "%{$kw}%")
          ->orWhere('model_name', 'like', "%{$kw}%");
      });
    }

    if ($request->filled('role') && $request->role !== 'all') {
      $role = $request->role;
      $query->whereHas('user', function ($q) use ($role) {
        $q->where('role', $role);
      });
    }

    // date range
    $range = $request->get('range', 'all');
    if ($range !== 'all') {
      if ($range === 'today') {
        $from = \Carbon\Carbon::today()->startOfDay();
        $to = \Carbon\Carbon::today()->endOfDay();
      } elseif ($range === '7') {
        $from = \Carbon\Carbon::today()->subDays(6)->startOfDay();
        $to = \Carbon\Carbon::today()->endOfDay();
      } elseif ($range === '30') {
        $from = \Carbon\Carbon::today()->subDays(29)->startOfDay();
        $to = \Carbon\Carbon::today()->endOfDay();
      } elseif ($range === 'custom' && $request->filled('from') && $request->filled('to')) {
        $from = \Carbon\Carbon::createFromFormat('Y-m-d', $request->from)->startOfDay();
        $to = \Carbon\Carbon::createFromFormat('Y-m-d', $request->to)->endOfDay();
      } else {
        $from = null;
        $to = null;
      }

      if ($from && $to) {
        $query->whereBetween('created_at', [$from, $to]);
      }
    }

    $activities = $query->get();

    $filename = 'activity_logs_' . date('Ymd_His') . '.csv';
    $headers = [
      'Content-Type' => 'text/csv',
      'Content-Disposition' => "attachment; filename=\"{$filename}\"",
    ];

    $columns = ['Waktu', 'User', 'Role', 'Action', 'Model', 'Model ID', 'Description', 'IP Address', 'User Agent'];

    $callback = function () use ($activities, $columns) {
      $file = fopen('php://output', 'w');
      fputcsv($file, $columns);
      foreach ($activities as $a) {
        $row = [
          $a->created_at->format('Y-m-d H:i:s'),
          $a->user ? $a->user->name : '-',
          $a->user ? $a->user->role : '-',
          $a->action,
          $a->model_name,
          $a->model_id,
          $a->description,
          $a->ip_address,
          $a->user_agent,
        ];
        fputcsv($file, $row);
      }
      fclose($file);
    };

    return response()->stream($callback, 200, $headers);
  }
}
