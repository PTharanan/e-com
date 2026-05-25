<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DeliveryMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            if (auth()->user()->role === 'delivery_boy') {
                return $next($request);
            }
            return redirect('/')->with('error', 'You do not have delivery access.');
        }

        return redirect()->route('delivery.login')->with('error', 'Please sign in to access the delivery area.');
    }
}
