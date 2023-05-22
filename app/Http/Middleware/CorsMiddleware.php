<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;

/**
 * cross origin resource sharing middleware
 *
 * @package App\Http\Middleware *
 */
class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param \Closure $next
     * @param string $except
     * @return mixed
     */
    public function handle($request, \Closure $next, $except = '')
    {
        if ($request instanceof Request && $this->except($request, $except)) {
            return $next($request);
        }

        $response = $next($request);
        $response->header('Access-Control-Allow-Methods','*');
        $response->header('Access-Control-Allow-Headers', '*');
        $response->header('Access-Control-Allow-Origin','*');

        return $response;
    }

    /**
     * @param Request $request
     * @param string|null $except
     * @return bool
     */
    private function except(Request $request, ?string $except): bool
    {
        if (!$except) {
            return false;
        }

        $uri = explode('?', $request->getUri(), 2)[0];
        $except = trim(str_ireplace('except:', '', $except), '/');

        return (bool)preg_match('@^(?:http://)?([^/]+)/('.$except.')/$@i', $uri.'/');
    }
}