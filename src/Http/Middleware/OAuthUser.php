<?php

namespace LemonCMS\LaravelCrud\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use LemonCMS\LaravelCrud\Exceptions\OAuthTokenExpired;

class OAuthUser extends AbstractOAuthMiddleware
{
    /**
     * @param Request $request
     * @param Closure $next
     * @param mixed ...$scopes
     * @return mixed
     * @throws OAuthTokenExpired
     */
    public function handle(Request $request, Closure $next, ...$scopes)
    {
        $this->handleUser($request, $scopes);

        return $next($request);
    }
}
