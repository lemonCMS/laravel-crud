<?php

namespace LemonCMS\LaravelCrud\Http\Middleware;

use Illuminate\Auth\AuthenticationException;
use Laravel\Passport\Http\Middleware\CheckClientCredentials;

class CheckApiCredentials extends CheckClientCredentials
{
    /**
     * Validate the scopes and token on the incoming request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $psr
     * @param array $scopes
     * @return void
     * @throws \Laravel\Passport\Exceptions\MissingScopeException|\Illuminate\Auth\AuthenticationException
     */
    protected function validate($psr, $scopes)
    {
        $token = $this->repository->find($psr->getAttribute('oauth_access_token_id'));

        if (! $token) {
            throw new AuthenticationException;
        }

        $this->validateScopes($token, $scopes);

        $userId = $psr->getAttribute('oauth_user_id', null);
        if ($userId !== null) {
            \Auth::loginUsingId($userId);
        }
    }
}
