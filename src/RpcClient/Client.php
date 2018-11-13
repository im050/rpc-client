<?php
/**
 * Created by PhpStorm.
 * User: memory
 * Date: 2018/9/3
 * Time: 上午10:30
 */

namespace Im050\RpcClient;

use Im050\RpcClient\Exception\ConnectException;
use Im050\RpcClient\Exception\IOException;
use Im050\RpcClient\Exception\RpcException;

class Client
{
    /**
     * 封包EOF字符串，需要和RPC服务端一致
     *
     * @var string
     */
    private $packageEof = "\r\n";

    /**
     * 建立连接的超时时间
     *
     * @var int
     */
    private $connectionTimeout = 3;

    /**
     * 读取数据流的超时时间
     *
     * @var int
     */
    private $readTimeout = 1;

    /**
     * 远程RPC服务地址
     *
     * @var string
     */
    private $remoteSocket = "tcp://127.0.0.1:8099";

    /**
     * 当前时间超过该值时抛出异常抛出异常
     *
     * @var
     */
    private $deadline;

    /**
     * Socket对象
     *
     * @var null
     */
    private $socket = null;

    /**
     * 错误码
     *
     * @var null
     */
    private $errno = null;

    /**
     * 错误信息
     *
     * @var null
     */
    private $errstr = null;

    /**
     * 连接RPC服务器
     */
    private function connect()
    {
        if (!$this->socket) {
            $this->socket = stream_socket_client($this->getRemoteSocket(), $this->errno, $this->errstr, $this->getConnectionTimeout());
        }
    }

    /**
     * 调用RPC方法
     *
     * @param string $interface
     * @param string $version
     * @param string $method
     * @param array $params
     * @return bool|string
     * @throws RpcException
     */
    public function call(string $interface, string $version, string $method, array $params = [])
    {
        $this->connect();
        if (!$this->socket) {
            throw new ConnectException("stream_socket_client fail errno={$this->errno} errstr={$this->errstr}");
        }
        $data = [
            'interface' => $interface,
            'version'   => $version,
            'method'    => $method,
            'params'    => $params,
            'logid'     => uniqid(),
            'spanid'    => 0,
        ];
        $data = json_encode($data, JSON_UNESCAPED_UNICODE) . $this->packageEof;
        fwrite($this->socket, $data);
        return $this->readLine(1024);
    }

    /**
     * 从IO流中读取一行
     *
     * @param int $bufferSize
     * @return string
     * @throws IOException
     */
    private function readLine($bufferSize)
    {
        $line = '';
        $this->deadline = time() + $this->getReadTimeout();
        while(!feof($this->socket)) {
            $line .= @fgets($this->socket, $bufferSize);
            $info = stream_get_meta_data($this->socket);
            if (!$this->deadline) {
                $default = (int)@ini_get('default_socket_timeout');
                stream_set_timeout($this->socket, $default > 0 ? $default : PHP_INT_MAX);
            } else {
                stream_set_timeout($this->socket, max($this->deadline - time(), 1));
            }
            if ($info['timed_out']) {
                throw new IOException("readLine() call timed out");
            }
            if (substr($line, -1) == "\n") {
                return rtrim($line, "\r\n");
            }
        }
        return $line;
    }

    /**
     * @return string
     */
    public function getPackageEof(): string
    {
        return $this->packageEof;
    }

    /**
     * @param string $packageEof
     * @return Client
     */
    public function setPackageEof(string $packageEof): Client
    {
        $this->packageEof = $packageEof;
        return $this;
    }

    /**
     * @return int
     */
    public function getConnectionTimeout(): int
    {
        return $this->connectionTimeout;
    }

    /**
     * @param int $connectionTimeout
     * @return Client
     */
    public function setConnectionTimeout(int $connectionTimeout): Client
    {
        $this->connectionTimeout = $connectionTimeout;
        return $this;
    }

    /**
     * @return int
     */
    public function getReadTimeout(): int
    {
        return $this->readTimeout;
    }

    /**
     * @param int $readTimeout
     * @return Client
     */
    public function setReadTimeout(int $readTimeout): Client
    {
        $this->readTimeout = $readTimeout;
        return $this;
    }

    /**
     * @return string
     */
    public function getRemoteSocket(): string
    {
        return $this->remoteSocket;
    }

    /**
     * @param string $remoteSocket
     * @return Client
     */
    public function setRemoteSocket(string $remoteSocket): Client
    {
        $this->remoteSocket = $remoteSocket;
        return $this;
    }

    /**
     * 关闭socket连接
     */
    public function close()
    {
        fclose($this->socket);
    }

    public function __destruct()
    {
        if ($this->socket) {
            $this->close();
        }
    }
}