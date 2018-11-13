<?php
/**
 * Created by PhpStorm.
 * User: memory
 * Date: 2018/9/3
 * Time: 上午10:35
 */

namespace Im050\RpcClient;


use Im050\RpcClient\Services\Services;

class ClientBuilder
{
    public static $instance = null;

    public static $services = [];

    private $client;

    private $host;

    private function __construct($host)
    {
        $this->client = new Client();
        $this->client->setRemoteSocket($host);
    }

    public static function instance($host = '')
    {
        if (!self::$instance instanceof ClientBuilder) {
            self::$instance = new self($host);
        }
        return self::$instance;
    }

    /**
     * 设置超时时间
     *
     * @param $connectionTimeout
     * @param null $readTimeout
     * @return $this
     */
    public function setTimeout($connectionTimeout, $readTimeout = null)
    {
        $this->client->setConnectionTimeout($connectionTimeout);
        if ($readTimeout !== null) {
            $this->client->setReadTimeout($readTimeout);
        }
        return $this;
    }

    /**
     * @param $name
     * @param $version
     * @return mixed
     */
    public function get($name, $version = null)
    {
        if (isset(self::$services[$name]) && self::$services[$name] instanceof Services) {
            return self::$services[$name];
        }
        if (class_exists($name)) {
            $services = new $name($this->client);
        } else {
            $services = new Services($this->client);
            $services->setInterface($name);
        }
        if ($version !== null) {
            $services->setVersion($version);
        }
        self::$services[$name] = $services;
        return self::$services[$name];
    }

    /**
     * @return mixed
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param mixed $client
     * @return ClientBuilder
     */
    public function setClient($client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param string $host
     * @return ClientBuilder
     */
    public function setHost(string $host): ClientBuilder
    {
        $this->host = $host;
        if ($this->client instanceof Client) {
            $this->client->setRemoteSocket($host);
        }
        return $this;
    }
}