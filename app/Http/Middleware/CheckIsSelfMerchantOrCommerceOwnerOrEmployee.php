<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckIsSelfMerchantOrCommerceOwnerOrEmployee
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
        if(isset(Auth::user()->merchant->id_public))
        {
            if(Auth::user()->merchant->id_public == $request->route()->parameter('idPublicMerchant'))
                return $next($request);
        }

        if(Auth::user()->hasRole(['ceo', 'cto', 'wolof.employee', 'commerce.owner']))
            return $next($request);

        return response()->json(['error' => 'Unauthorized - No tiene permiso'], 403);
    }
}
