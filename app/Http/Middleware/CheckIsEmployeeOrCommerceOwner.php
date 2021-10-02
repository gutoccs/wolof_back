<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckIsEmployeeOrCommerceOwner
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
        if(Auth::user()->hasRole(['ceo', 'cto', 'gabu.employee', 'commerce.owner']))
            return $next($request);

        return response()->json(['error' => 'Unauthorized - No tiene permiso'], 403);
    }
}
