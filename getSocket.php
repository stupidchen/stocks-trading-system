<?php
set_time_limit(0);

$ip = '192.168.220.130';
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
		$msg = "Connection $count success!";
		$count++;
		socket_write($msgsock,$msg,strlen($msg));

		$buf = socket_read($msgsock,8192);
		if (strcmp($buf,":halt") == 0) break;

		$feedback = "No.$count connection received :$buf\n";
		echo $feedback;

	}
	socket_close($msgsock);
}
while (true);
socket_close($sock);
?>
