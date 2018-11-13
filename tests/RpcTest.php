<?php
/**
 * Created by PhpStorm.
 * User: memory
 * Date: 2018/11/13
 * Time: 下午3:43
 */
use PHPUnit\Framework\TestCase;

class RpcTest extends TestCase
{
    public function test()
    {
        $host = 'tcp://127.0.0.1:8099';
        $factory = \Im050\RpcClient\ClientBuilder::instance($host);
        $demoService = $factory->get('App\\Lib\\DemoInterface');
        $result = $demoService->getUser(1);
        $this->assertEquals(["1"], $result);
        //try to change version of interface
        $result = $demoService->setVersion("1.0.1")->getUser(1);
        $this->assertEquals(["1", "version"], $result);
    }
}
