<?php

namespace App\Http\Middleware;

use App\Models\Store;

use Closure;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class storeExist
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $store = Store::find(request()->route('id'));
        if (!$store) {
            return response([
                'status' => 'failed',
                'message' => 'The specific store does not exist.'
            ], 404);
        }

        request()->merge(['store' => $store]);

        return $next($request);
    }
}
