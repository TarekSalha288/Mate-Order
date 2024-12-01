<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleWare
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(auth()->check()){
            if(auth()->user()->status_role=='admin'){
                return $next($request);
            }

            return response()->json(['message'=>'You Can\'t Access This Pages ']);
        }
        return response()->json(['message'=>'You Should LogIn ']);
    }
}
