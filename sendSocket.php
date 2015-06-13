<?
//create connect write read close
set_time_limit(0);

$ip = '127.0.0.1';
$port = 1935;

$socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
if ($socket < 0) echo "socket_create() failed.The reason is ".socket_strerror($socket). "\n";

$result=socket_connect($socket,$ip,$port);
if ($result < 0) echo "socket_connect() failed.The reason is ".socket_strerror($result). "\n";

$sendMsg="Core system boot success!</br>";
$ret=socket_write($socket,$sendMsg,strlen($sendMsg));

if (!$ret){
	echo "socket_write() failed.The reason is ".socket_strerror($result)."\n";
}
else{
	echo "Message send successfully!</br>";
}

while ($out = socket_read($socket,8192)){
	echo "Message received! The message is ";
	echo "<font color='red'>$out</font></br>";
}

socket_close($socket);
?>