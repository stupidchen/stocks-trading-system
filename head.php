<?php
public $serverIP = '127.0.0.1';
public $serverPort = '1935';
public $daySegment = 3600*24;
public $hourSegment = 3600;
function getTimeStamp(){
	$timeNow=microTime();
	list($msec,$sec)=explode(" ",$timeNow);
	return ((float)$msec + (float)$sec);
}
