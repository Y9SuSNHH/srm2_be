<?php

namespace App\Http\Middleware;

class RegisterRepositoryMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param $request
     * @param \Closure $next
     * @param string $domain
     * @return mixed
     */
    public function handle($request, \Closure $next,string $domain)
    {
        app()->registerRepositoryServiceProvider($domain);

        return $next($request);
    }
}
