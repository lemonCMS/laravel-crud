<?php

namespace LemonCMS\LaravelCrud\Factories;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Auth\Access\AuthorizationException;

class OAuthClient
{
    /**
     * @var string
     */
    protected $access_token;
    /**
     * @var int
     */
    protected $expires_in;
    /**
     * @var string
     */
    protected $token_type;

    /**
     * OAuthClient constructor.
     */
    public function __construct()
    {
        $this->setUp();
    }

    /**
     * @return string
     */
    public function setUp()
    {
        // Token is about to expire refresh it.
        // Let's hope the servers have no time difference more then one minute.
        if (\Cache::has('apiClient') && $this->access_token && $this->expires_in <= time() + 60) {
            \Cache::clear('apiClient');
        }

        $response = \Cache::remember('apiClient', 300, function () {
            $guzzle = new Client();

            $response = $guzzle->post(config('oauth.host').config('oauth.token'), [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => config('oauth.client_id'),
                    'client_secret' => config('oauth.client_secret'),
                    'scope' => config('oauth.scopes'),
                ],
            ]);

            $data = json_decode((string) $response->getBody());

            if (! $data->access_token) {
                throw new AuthorizationException('Api client could not get authorized');
            }

            return $data;
        });

        $this->access_token = $response->access_token;
        $this->expires_in = $response->expires_in;
        $this->token_type = $response->token_type;

        return $this->access_token;
    }

    /**
     * @param string $method
     * @param string $domain
     * @param string $path
     * @param array $params
     * @return mixed
     * @throws \Exception
     */
    public function client(string $method, string $domain, string $path, array $params = [])
    {
        $user = \Request::user();
        $params = array_replace_recursive([
            'headers' => [
                'Authorization' => 'Bearer '.$this->access_token,
                'Accept' => 'application/json',
                'X-USER-ID' => $user ? $user->id : null,
                'X-CLIENT-ID' => config('oauth.client_id'),
            ],
        ], $params);

        $guzzle = new Client();
        /** @var Response $response */
        $response = call_user_func([$guzzle, $method], $domain.$path, $params);

        if ($response->getStatusCode() < 200 || $response->getStatusCode() > 299) {
            throw new \Exception('Shit hit the fan');
        }
        if (in_array($response->getHeader('Content-Type'), ['application/json'])) {
            return json_decode((string) $response->getBody());
        }

        return $response->getBody()->getContents();
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->access_token;
    }

    /**
     * @return string
     */
    public function getExpiresIn()
    {
        return $this->expires_in;
    }

    /**
     * @return string
     */
    public function getTokenType()
    {
        return $this->token_type;
    }
}
