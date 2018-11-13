<?php
/**
 * Created by PhpStorm.
 * User: memory
 * Date: 2018/9/3
 * Time: 上午10:39
 */

namespace Im050\RpcClient\Services;

use Im050\RpcClient\Client;
use Im050\RpcClient\Exception\ResponseException;
use Im050\RpcClient\Exception\RpcException;

class Services
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    public $interface;

    /**
     * @var string
     */
    public $version = '0';

    /**
     * Services constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->setClient($client);
    }

    /**
     * @return Client
     */
    public function getClient() : Client
    {
        return $this->client;
    }

    /**
     * @param mixed $client
     * @return Services
     */
    public function setClient(Client $client) : Services
    {
        $this->client = $client;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getInterface()
    {
        return $this->interface;
    }

    /**
     * @param mixed $interface
     * @return Services
     */
    public function setInterface($interface)
    {
        $this->interface = $interface;
        return $this;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @param string $version
     * @return Services
     */
    public function setVersion(string $version): Services
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @param $method
     * @param $params
     * @param $version
     * @return bool|string
     * @throws RpcException
     */
    public function call($method, $params = array(), $version = false) {
        if ($version === false)
        {
            $version = $this->version;
        }
        $message = $this->getClient()->call($this->interface, $version, $method, $params);
        $response = json_decode($message, true);
        if ($response == null) {
            throw new ResponseException("Explain json data failed with \"{$message}\"");
        }
        if ($response['status'] != 200) {
            throw new ResponseException($response['msg']);
        }

        return $response['data'];
    }

    /**
     * @param $name
     * @param $arguments
     * @return bool|string
     * @throws RpcException
     */
    public function __call($name, $arguments)
    {
        return $this->call($name, $arguments);
    }

}