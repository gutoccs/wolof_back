<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckIsSelfEmployeeOrLevel10
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

        if(isset(Auth::user()->employee->id))
        {
            if(Auth::user()->employee->id == $request->route()->parameter('idEmployee'))
                return $next($request);
        }

        if(Auth::user()->level() == 10)
            return $next($request);

        return response()->json(['error' => 'Unauthorized - Recurso no le pertenece'], 403);
    }
}
