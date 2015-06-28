<?php
use Workerman\Worker;
use \Workerman\WebServer;
use \GatewayWorker\Gateway;
use \GatewayWorker\BusinessWorker;
use \Workerman\Autoloader;
require_once __DIR__ . '/Workerman/Autoloader.php';
Autoloader::setRootPath(__DIR__);
//以守护进程运行worker
 Worker::$daemonize = true;
// bussinessWorker 进程
$worker = new BusinessWorker();
// worker名称
$worker->name = 'CliTesBusinessWorker';
// bussinessWorker进程数量
$worker->count = 4;

// gateway 进程(监听)
$gateway = new Gateway("tcp://127.0.0.1:1500");
// gateway名称，status方便查看
$gateway->name = 'CliTesGateway';
// gateway进程数
$gateway->count = 4;
// 本机ip，分布式部署时使用内网ip
$gateway->lanIp = '127.0.0.1';
// 内部通讯起始端口，假如$gateway->count=4，起始端口为4000
// 则一般会使用4001 4002 4003 4004 4个端口作为内部通讯端口 
$gateway->startPort = 2000;//内部通讯端口
// 心跳间隔
$gateway->pingInterval = 10;
// 心跳数据
$gateway->pingData = '{"type":"ping"}';



// 运行所有服务
Worker::runAll();
