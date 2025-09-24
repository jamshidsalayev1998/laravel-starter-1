<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserBan
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->isBanned()) {
            $banInfo = $user->getBanInfo();
            
            return response()->json([
                'success' => false,
                'message' => 'Account is banned',
                'ban_info' => $banInfo
            ], 403);
        }

        return $next($request);
    }
}
