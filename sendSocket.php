<?php
//create connect write read close
set_time_limit(0);

$ip = '127.0.0.1';
$port = 1935;

$socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
if ($socket < 0) echo "socket_create() failed.The reason is ".socket_strerror($socket). "\n";

$result=socket_connect($socket,$ip,$port);
if ($result < 0) echo "socket_connect() failed.The reason is ".socket_strerror($result). "\n";

$sendArray=array(
'type' => 0,
'id' => NULL,
'aid' => 'testCoreSystem0',
'code' => 100,
'amount' => 10,
'price' => 70,
'time' => NULL
);
$sendMsg=json_encode($sendArray);
$ret=socket_write($socket,$sendMsg,strlen($sendMsg));

if (!$ret){
	echo "socket_write() failed.The reason is ".socket_strerror($result)."\n";
}
else{
	echo "Message send successfully!\n";
}

while ($out = socket_read($socket,8192)){
	echo "Message received! The message is ";
	echo "$out";
}

socket_close($socket);
?>
