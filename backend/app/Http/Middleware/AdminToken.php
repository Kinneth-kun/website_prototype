<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class AdminToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        $session = $token ? DB::table('admin_sessions')
            ->where('token_hash', hash('sha256', $token))->where('expires_at', '>', now())->first() : null;
        $idleMinutes = max(5, (int) env('ADMIN_IDLE_TIMEOUT_MINUTES', 30));
        if ($session && $session->last_used_at && Carbon::parse($session->last_used_at)->lte(now()->subMinutes($idleMinutes))) {
            DB::table('admin_sessions')->where('id', $session->id)->delete();
            $session = null;
        }
        $user = $session ? User::whereKey($session->user_id)->where('status', 'active')->first() : null;
        if (! $user) return response()->json(['message' => 'Unauthenticated.'], 401);
        if ($session && (! $session->last_used_at || now()->diffInMinutes($session->last_used_at) >= 5)) {
            DB::table('admin_sessions')->where('id', $session->id)->update(['last_used_at' => now(), 'updated_at' => now()]);
        }
        $request->attributes->set('admin_session_id', $session->id);
        auth()->setUser($user);
        return $next($request);
    }
}
