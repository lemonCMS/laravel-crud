<?php

namespace LemonCMS\LaravelCrud\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OAuthClient extends AbstractOAuthMiddleware
{
    /**
     * @param Request $request
     * @param Closure $next
     * @param mixed ...$scopes
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$scopes)
    {
        $this->handleClient();

        return $next($request);
    }
}
