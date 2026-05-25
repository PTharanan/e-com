<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SellerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            if (auth()->user()->role === 'seller') {
                if (auth()->user()->is_blocked) {
                    auth()->logout();
                    return redirect()->route('login')->with('error', 'Your account has been blocked by the store administrator.');
                }
                return $next($request);
            }
            return redirect('/')->with('error', 'You do not have seller access.');
        }

        return redirect()->route('seller.login')->with('error', 'Please sign in to access the seller area.');
    }
}
