<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Carbon\Carbon;

class UserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check())
            {
                $user = Auth::user();
                $cacheKey = 'user-is-online-'.$user->id;
                
                if (!Cache::has($cacheKey))
                    {
                        $user->last_seen_at = now();
                        //for avoiding triggering other events will use saveQuietly
                        $user->saveQuietly();
                        //reseting the chache timer for 5 min
                        Cache::put($cacheKey,true,now()->addMinutes(5));
                    }
            }
        return $next($request);
    }
}
