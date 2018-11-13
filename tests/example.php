<?php
/**
 * Created by PhpStorm.
 * User: memory
 * Date: 2018/11/13
 * Time: 下午2:24
 */
include dirname(dirname(__FILE__)) . '/vendor/autoload.php';
include 'DemoServices.php';

$host = "tcp://127.0.0.1:8099";

//实例化一个service工厂
$factory = \Im050\RpcClient\ClientBuilder::instance();
$factory->setHost($host);

//获取demoService实例
$demoService = $factory->get("App\\Lib\\DemoInterface");

//调用demoService的getUser方法
$results = $demoService->getUsers([1,2,3,4,5,6]);

//调用demoService的getUserByCond方法
$results2 = $demoService->getUserByCond(1, 1, "lin", 1.2);

//改变demoService版本号
$demoService->setVersion('1.0.1');

//调用demoService的getUsers方法
$results3 = $demoService->getUsers([1,2]);

//通过类的方式创建demoService实例
$demoServiceV2 = $factory->get(DemoServices::class);

//获取demoServiceV2的用户
$results4 = $demoServiceV2->getUser(1);

var_dump($results, $results2, $results3, $results4);