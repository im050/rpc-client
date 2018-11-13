<?php
/**
 * Created by PhpStorm.
 * User: memory
 * Date: 2018/11/13
 * Time: 下午2:49
 */

class DemoServices extends \Im050\RpcClient\Services\Services
{
    /**
     * 定义接口
     *
     * @var string
     */
    public $interface = "App\\Lib\\DemoInterface";

    /**
     * 定义版本号
     *
     * @var string
     */
    public $version = '1.0.1';
}