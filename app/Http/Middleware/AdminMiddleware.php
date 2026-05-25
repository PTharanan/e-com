<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            if (auth()->user()->role === 'admin') {
                if (auth()->user()->is_blocked) {
                    auth()->logout();
                    return redirect()->route('admin.login')->with('error', 'Your admin account has been suspended.');
                }
                return $next($request);
            }
            return redirect('/')->with('error', 'You do not have admin access.');
        }

        return redirect()->route('admin.login')->with('error', 'Please sign in to access the admin area.');
    }
}
