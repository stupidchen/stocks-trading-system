<?php
use Workerman\Worker;
use \Workerman\WebServer;
use \GatewayWorker\Gateway;
use \GatewayWorker\BusinessWorker;
use \Workerman\Autoloader;
require_once __DIR__ . '/Workerman/Autoloader.php';
require_once __DIR__ . 'controller.php';
Autoloader::setRootPath(__DIR__);

$gateway = new Gateway("tcp://127.0.0.1:1935");
$gateway->name = 'CoreSystemGateway';
$gateway->count = 4;
$gateway->lanIp = '127.0.0.1';
$gateway->startPort = 2000;
$gateway->pingInterval = 10;
$gateway->pingData = '{"type":"ping"}';

Worker::$daemonize = true;
$worker = new Worker("tcp://127.0.0.1:4000");
$worker->name = 'CoreSystemController';
$worker->count = 4;
$worker->onMessage = function($connection, $message){
	$messageData = json_decode($message, true);
	$newInstruction = new allInstruction($messageData['time'], $messageData['aid'], $messageData['code'], $messageData['amount'], $messageData['price'], $messageData['type']);
	$sysController->process($newInstruction);
}
$sysController = new controller();

Worker::runAll();
