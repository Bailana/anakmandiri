<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   */
  public function handle(Request $request, Closure $next, ...$roles): Response
  {
    if (!auth()->check()) {
      return redirect()->route('login');
    }

    $userRole = auth()->user()->role ?? '';
    foreach ($roles as $r) {
      // exact match
      if ($r === $userRole) return $next($request);
      // allow matching when user's role contains the allowed role (e.g. "konsultan pendidikan" should match "konsultan")
      if ($r && stripos($userRole, $r) !== false) return $next($request);
    }

    return abort(403, 'Anda tidak memiliki akses ke halaman ini.');
  }
}
