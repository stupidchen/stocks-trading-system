<?php
function getTimeStamp(){
	$timeNow=microTime();
	list($msec,$sec)=explode(" ",$timeNow);
	return ((float)$msec + (float)$sec);
}
//create connect write read close
set_time_limit(0);

$ip = '127.0.0.1';
$port = 1935;



$sendArray=array(
'type' => 0,
'id' => NULL,
'aid' => 'testCoreSystem',
'code' => 100002,
'amount' => 2000,
'price' => 10,
'time' => NULL,
'msec' => NULL
);
$sendMsg=json_encode($sendArray);

$startTime=getTimeStamp();
for ($i=0;$i<1;$i++){
$socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
if ($socket < 0) echo "socket_create() failed.The reason is ".socket_strerror($socket). "\n";

$result=socket_connect($socket,$ip,$port);
if ($result < 0) echo "socket_connect() failed.The reason is ".socket_strerror($result). "\n";
$ret=socket_write($socket,$sendMsg,strlen($sendMsg));
/*
if (!$ret){
	echo "socket_write() failed.The reason is ".socket_strerror($result)."\n";
}
else{
	echo "Message send successfully!\n";
}

echo "Message received! The message is ";
echo "$out";*/

socket_close($socket);
}
$endTime=getTimeStamp();
echo round($endTime-$startTime,4)."s\n";

?>
