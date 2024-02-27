<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class hasStore
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!request()->user()->email_verified_at) {
            return response([
                
                'status' => 'failed',
                'message' => 'Please verify your email first.'
            ], 403);
        }
        if (!request()->user()->store) {
            return response([
                'status' => 'failed',
                'message' => 'Please create a store first.'
            ], 401);
        }
        return $next($request);
    }
}
