<?php
namespace AtLab\Comagic;

use GuzzleHttp\Client;
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
    private $_entryPoint = 'https://callapi.comagic.ru/';

    /**
     * Call API version to use
     *
     * @var string
     */
    private $_version = 'v4.0';

    /**
     * Call API access token
     *
     * @var string
     */
    private $_accessToken = 'sad';

    /**
     * Call API access token expiration time
     *
     * @var string
     */
    private $_accessTokenExpires = null;

    /**
     * Call API login
     *
     * @var string
     */
    private $_login = null;

    /**
     * Call API password
     *
     * @var string
     */
    private $_password = null;

    /**
     * Call API Guzzle client
     *
     * @var \CoMagic\GuzzleHttp
     */
    private $_client = null;

    /**
     * Call API last response metadata
     *
     */
    private $_metadata = null;

    /**
     * Init CoMagic Call API client
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        if (!(isset($config['access_token']) ||
            isset($config['login']) && isset($config['password'])))
        {
            throw new \Exception('Access token and/or login+password required');
        }

        if (!empty($config['endpoint']['call_api'])) {
            $this->_entryPoint = $config['endpoint']['call_api'];
        }

        $this->_client = new Client([
            'base_uri' => rtrim($this->_entryPoint, '/') .
                '/' . $this->_version,
            'headers' => [
                'Accept' => 'application/json',
                'Content-type' => 'application/json; charset=UTF-8'
            ]
        ]);

        if (!empty($config['access_token'])) {
            $this->_accessToken = $config['access_token'];
        }

        if (!empty($config['login']) && !empty($config['password']))
        {
            $this->_login    = $config['login'];
            $this->_password = $config['password'];
        }
    }
    /**
     * Set the IoC Container.
     *
     * @param  ContainerInterface  $container Container instance
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
            $this->_accessTokenExpires > (time() + 60)))
        {
            return true;
        }

        $data = $this->_doRequest(
            'login.user',
            [
                'login'    => $this->_login,
                'password' => $this->_password
            ]
        );

        $this->_accessToken        = $data->access_token;
        $this->_accessTokenExpires = $data->expire_at;
    }

    /**
     * Get last response metadata
     *
     * @return string
     */
    public function metadata()
    {
        return $this->_metadata;
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
        if (isset($arguments[0]))
        {
            $params = array_merge($params, $arguments[0]);
        }

        return $this->_doRequest($method, $params);
    }

    private function _doRequest($method, $params)
    {
        $payload = [
            'jsonrpc' => '2.0',
            'id' => time(),
            'method' => $method,
            'params' => $params
        ];

        try
        {
            $response = $this->_client->post('', ['json' => $payload]);

            $responseBody = json_decode($response->getBody());

            if (isset($responseBody->result))
            {
                $this->_metadata = $responseBody->result->metadata;
            }

            if (isset($responseBody->error))
            {
                throw new \Exception(
                    $responseBody->error->code . ' ' .
                        $responseBody->error->message
                );
            }

            return $responseBody->result->data;
        }
        catch (\CoMagic\TransferException $e)
        {
            throw new \Exception($e->getMessage());
        }
    }
}
