<?php

namespace LemonCMS\LaravelCrud\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use LemonCMS\LaravelCrud\Exceptions\OAuthTokenExpired;

class OAuth extends AbstractOAuthMiddleware
{
    /**
     * @param Request $request
     * @param Closure $next
     * @param mixed ...$scopes
     * @return mixed
     * @throws OAuthTokenExpired
     * @throws \LemonCMS\LaravelCrud\Exceptions\OAuthTokenInvalid
     */
    public function handle(Request $request, Closure $next, ...$scopes)
    {
        $this->handleClient();
        $this->handleUser($request, $scopes);

        return $next($request);
    }
}
