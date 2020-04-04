<?php

namespace LemonCMS\LaravelCrud\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use LemonCMS\LaravelCrud\Exceptions\OAuthScopeInvalid;
use LemonCMS\LaravelCrud\Exceptions\OAuthTokenExpired;
use LemonCMS\LaravelCrud\Exceptions\OAuthTokenInvalid;
use LemonCMS\LaravelCrud\Services\AccountService;

abstract class AbstractOAuthMiddleware
{
    /**
     * @param Request $request
     * @param Closure $next
     * @param mixed ...$scopes
     * @return mixed
     */
    abstract public function handle(Request $request, Closure $next, ...$scopes);

    protected function handleClient()
    {
        \OAuthClient::setUp();
    }

    /**
     * @param Request $request
     * @param $scopes
     * @throws OAuthTokenExpired
     * @throws OAuthTokenInvalid
     */
    protected function handleUser(Request $request, $scopes)
    {
        $token = (new Parser())->parse($request->bearerToken());
        $publicKey = new Key('file://'.storage_path('oauth-public.key'));

        if ($token->verify(new Sha256(), $publicKey) === false) {
            throw new OAuthTokenInvalid();
        }

        if ($token->isExpired()) {
            throw new OAuthTokenExpired();
        }

        $allScopes = collect($token->getClaim('scopes'))->filter(function ($scope) {
            return $scope === '*';
        })->isNotEmpty();

        if ($allScopes === false) {
            collect($scopes)->each(function ($scope) use ($token) {
                if (! in_array($scope, $token->getClaim('scopes'))) {
                    throw new OAuthScopeInvalid('Missing scope: '.$scope);
                }
            });
        }

        $user = (new AccountService)->getUser($request->bearerToken());
        $request->setUserResolver(function () use ($user) {
            return $user;
        });
    }
}
