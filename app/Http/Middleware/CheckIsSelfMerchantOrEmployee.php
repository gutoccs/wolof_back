<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckIsSelfMerchantOrEmployee
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if(isset(Auth::user()->merchant->id))
        {
            if(Auth::user()->merchant->id == $request->route()->parameter('idMerchant'))
                return $next($request);
        }

        if(Auth::user()->employee)
            return $next($request);

        return response()->json(['error' => 'Unauthorized - Recurso no le pertenece'], 403);
    }
}
