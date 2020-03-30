<?php

namespace LemonCMS\LaravelCrud\Services;

class AccountService extends ApiService
{
    public function getUser($token)
    {
        $headers = [
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ];

        return $this->request('get', 'profile', [], [], $headers);
    }

    public function getClient()
    {
        return $this->request('get', 'client');
    }

    protected function getBaseUrl()
    {
        return config('oauth.host').'/api/';
    }
}
