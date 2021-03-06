<?php
use \Workerman\Worker;
use \Workerman\WebServer;
use \GatewayWorker\Gateway;
use \GatewayWorker\BusinessWorker;
use \Workerman\Autoloader;
require_once __DIR__ . '/Workerman/Autoloader.php';
require_once __DIR__ . '/controller.php';
Autoloader::setRootPath(__DIR__);

$GLOBALS['CU'] = new controller();

Worker::$daemonize = true;
addLog('Booting...');
$bWorker = new BusinessWorker();
$bWorker->name = 'UselessWorker';
$bWorker->count = 1;
$workerInst = new Worker("tcp://127.0.0.1:4000");
$workerInst->name = 'CoreSystemController';
$workerInst->count = 1;
$workerInst->onMessage = function($connection, $message){
	addLog('Worker:Received message!');
	$messageData = json_decode($message, true);
	$newInstruction = new instructions($messageData['time'], $messageData['id'], $messageData['aid'], $messageData['code'], $messageData['amount'], $messageData['price'], $messageData['type'], NULL, $messageData['msec']);
	addLog('Worker:Prepare to process the instruction!');
	$cu = $GLOBALS['CU'];
	$cu->process($newInstruction);
};

$gateway = new Gateway("tcp://127.0.0.1:1935");
$gateway->name = 'CoreSystemGateway';
$gateway->count = 4;
$gateway->lanIp = '127.0.0.1';
$gateway->startPort = 2000;
$gateway->pingInterval = 10;
$gateway->pingData = '';

Worker::runAll();
