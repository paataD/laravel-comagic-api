<?php

namespace AtLab\Comagic;

use AtLab\Comagic\Exceptions\DataApiException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Psr\Container\ContainerInterface;

/**
 * @method static listCalls()
 */
class Api
{
    /**
     * Call API entry point
     *
     * @var string
     */
    private string $host;

    /**
     * Call API version to use
     *
     * @var string
     */
    private string $version;


    /**
     * Call API login
     */
    private string|null $_login = null;

    /**
     * Call API password
     *
     */
    private string|null $_password = null;

    /**
     * Call API Guzzle client
     *
     * @var \CoMagic\GuzzleHttp
     */
    private $client = null;

    /**
     * Call API last response metadata
     *
     */
    private $metadata = null;

    /**
     * Init CoMagic Call API client
     *
     */
    public function __construct()
    {
        $this->version = config('comagic.api_v');
        $this->host = config('comagic.host');

        if (!(config('comagic.access_token') ||
            config('comagic.login') && config('comagic.login'))) {
            throw new \Exception('Access token and/or login+password required');
        }

        $this->client = new Client([
            'base_uri' => rtrim($this->host, '/') .
                '/' . $this->version,
            'headers' => [
                'Accept' => 'application/json',
                'Content-type' => 'application/json; charset=UTF-8'
            ]
        ]);

        if (!empty(config('comagic.access_token'))) {
            $this->_accessToken = config('comagic.access_token');
        }

        if (!empty(config('comagic.login')) && !empty(config('comagic.password'))) {
            $this->_login = config('comagic.login');
            $this->_password = config('comagic.password');
        }
    }

    /**
     * Set the IoC Container.
     *
     * @param ContainerInterface $container Container instance
     */
    public function setContainer(ContainerInterface $container): self
    {
        $this->container = $container;

        return $this;
    }

    private function _checkLogin()
    {
        // Check if access token is not expired
        if ($this->_accessToken && (is_null($this->_accessTokenExpires) ||
                $this->_accessTokenExpires > (time() + 60))) {
            return true;
        }

        $data = $this->_doRequest(
            'login.user',
            [
                'login' => $this->_login,
                'password' => $this->_password
            ]
        );

        $this->_accessToken = $data->access_token;
        $this->_accessTokenExpires = $data->expire_at;
    }

    /**
     * Get last response metadata
     *
     * @return string
     */
    public function metadata()
    {
        return $this->metadata;
    }

    /**
     * Magic method for API calls
     *
     * @param string $camelCaseMethod
     * @param array $arguments
     * @return mixed
     * @throws \CoMagic\Exception
     */
    public function __call($camelCaseMethod, $arguments)
    {
        $this->_checkLogin();

        $camelCaseMethod = preg_replace(
            '~(.)(?=[A-Z])~',
            '$1_',
            $camelCaseMethod
        );

        $method = strtolower(preg_replace('~_~', '.', $camelCaseMethod, 1));


        $params = ['access_token' => $this->_accessToken];
        if (isset($arguments[0])) {
            $params = array_merge($params, $arguments[0]);
        }

        return $this->_doRequest($method, $params);
    }

    /**
     * @throws DataApiException
     */
    private function _doRequest($method, $params)
    {
        $payload = [
            'jsonrpc' => '2.0',
            'id' => time(),
            'method' => $method,
            'params' => $params
        ];
        $methodParts = explode('.', $method, 2);
        try {
            if (config('comagic.debug')) {
                Log::debug(json_encode($payload, JSON_PRETTY_PRINT));
            }

            $response = $this->client->post('', ['json' => $payload]);

            $responseBody = json_decode($response->getBody());

            if (isset($responseBody->result)) {
                $this->metadata = $responseBody->result->metadata;
            }

            if (isset($responseBody->error)) {
                throw new DataApiException($methodParts[0], $responseBody);
            }

            return $responseBody->result->data;
        } catch (\CoMagic\TransferException $e) {
            throw new \Exception($e->getMessage());
        }
    }
}