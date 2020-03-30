<?php

namespace LemonCMS\LaravelCrud\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use LemonCMS\LaravelCrud\Exceptions\OAuthScopeInvalid;
use LemonCMS\LaravelCrud\Exceptions\OAuthTokenExpired;
use LemonCMS\LaravelCrud\Services\AccountService;

class OauthProvider
{
    /**
     * @param Request $request
     * @param Closure $next
     * @param array $scopes
     * @return mixed
     * @throws OAuthTokenExpired
     */
    public function handle(Request $request, Closure $next, ...$scopes)
    {
        \OAuthClient::setUp();
        list(, $body) = explode('.', $request->bearerToken());
        $body = Jwt::jsonDecode(Jwt::urlsafeB64Decode($body));

        if ($body->exp < time()) {
            throw new OAuthTokenExpired('The token is expired');
        }

        collect($scopes)->each(function ($scope) use ($body) {
            if (! in_array($scope, $body->scopes)) {
                throw new OAuthScopeInvalid('Missing scope: '.$scope);
            }
        });

        $user = (new AccountService)->getUser($request->bearerToken());
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        return $next($request);
    }
}
