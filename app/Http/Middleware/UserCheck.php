<?php

namespace App\Http\Middleware;

use Closure;
use Cache;

class UserCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        $token = $request->input('token');
        if (empty($token)) {
            throw new Exception('请传入token');
        }

        $user = Cache::get($token);

        if (empty($user)) {
            throw new Exception('请先登录');
        }

        return $next($request);
    }
}
