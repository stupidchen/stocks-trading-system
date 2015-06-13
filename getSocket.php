<?php
set_time_limit(0);

$ip = '127.0.0.1';
$port = 1935;

if (($sock = socket_create(AF_INET,SOCK_STREAM,SOL_TCP)) < 0){
	echo "scoket_create() failed. The reason is :".socket_strerror($sock)."\n";
}
if (($ret = socket_bind($sock,$ip,$port)) < 0){
	echo "scoket_bind() failed. The reason is :".socket_strerror($ret)."\n";
}
if (($ret = socket_listen($sock,4)) < 0){
	echo "scoket_listen() failed. The reason is :".socket_strerror($ret)."\n";
}

$count = 0;

do{
	if (($msgsock = socket_accept($sock))<0) {
		echo "socket_accept() failed. The reason is ".socket_strerror($msgsock)."\n";
		break;
	}
	else{
		$msg = "Connection success!\n";
		socket_write($msgsock,$msg,strlen($msg));

		echo "hello world!\n";
		$buf = socket_read($msgsock,8192);

		$feedback = "received :$buf\n";
		echo $feedback;

		if ($count++ >= 5){
			break;
		}
	}
	socket_close($msgsock);
}
while (true);
socket_close($sock);
?>
