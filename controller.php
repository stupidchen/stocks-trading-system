<?php 
require("./pool.php");
//require("./input.php");
//require("./database.php");
class controller{
	public $ip='127.0.0.1';
	public $port='1935';
	public $bootTime;
	public $poolUnit,$databaseUnit,$inputUnit;
	public function controller(){
		$this->poolUnit = new pool();
//		$this->databaseUnit = new database();
//		$this->inputUnit = new inputUnit();
		$this->bootTime = time();
	}
	public function process(){
//		$inputUnit->run();
		for ($i = 0; $i < 5; $i++){
			$timeNow = time();
			$result = $this->poolUnit->addIns($timeNow ,0 ,100 ,100 ,0 ,0 );
			$result = $this->poolUnit->addIns($timeNow, 0 ,100 ,101 ,0 ,1 );
			$result = $this->poolUnit->addIns($timeNow ,0 ,100 ,100 ,0 ,1 );
			echo $result->num."\n";
		}
	}
}

$globalController=new controller();
$globalController->process();
?>
