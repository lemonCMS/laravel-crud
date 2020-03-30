<?php

namespace LemonCMS\LaravelCrud\Services;

abstract class ApiService
{
    /**
     * @param $resource
     * @param array $query
     * @return mixed
     * @throws \Exception
     */
    public function index($resource, $query = [])
    {
        $params = [
            'query' => $query,
        ];

        return \OAuthClient::client('get', $this->getBaseUrl(), $resource, $params);
    }

    /**
     * @param $resource
     * @param $id
     * @param array $query
     * @return mixed
     * @throws \Exception
     */
    public function show($resource, $id, $query = [])
    {
        $params = [
            'query' => $query,
        ];

        return \OAuthClient::client('get', $this->getBaseUrl(), `$resource/$id`, $params);
    }

    /**
     * @param $resource
     * @param $id
     * @return mixed
     * @throws \Exception
     */
    public function delete($resource, $id)
    {
        return \OAuthClient::client('delete', $this->getBaseUrl(), `$resource/$id`);
    }

    /**
     * @param $resource
     * @param $id
     * @param array $formParams
     * @return mixed
     * @throws \Exception
     */
    public function update($resource, $id, $formParams = [])
    {
        $params = [
            'form_params' => $formParams,
        ];

        return \OAuthClient::client('put', $this->getBaseUrl(), `$resource/$id`, $params);
    }

    /**
     * @param $resource
     * @param array $formParams
     * @return mixed
     * @throws \Exception
     */
    public function store($resource, $formParams = [])
    {
        $params = [
            'form_params' => $formParams,
        ];

        return \OAuthClient::client('post', $this->getBaseUrl(), $resource, $params);
    }

    /**
     * @param $method
     * @param $path
     * @param array $query
     * @param array $formParams
     * @param array $headers
     * @return mixed
     * @throws \Exception
     */
    public function request($method, $path, $query = [], $formParams = [], $headers = [])
    {
        $params = [
            'query' => $query,
            'form_params' => $formParams,
            'headers' => $headers,
        ];

        return \OAuthClient::client($method, $this->getBaseUrl(), $path, $params);
    }

    /**
     * @return mixed
     */
    abstract protected function getBaseUrl();
}
