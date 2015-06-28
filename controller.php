<?php 
require_once __DIR__.'/pool.php';
require_once __DIR__.'/database.php';
const serverIP = '127.0.0.1';
const serverPort = '1935';
const daySegment = 86400; //3600*24
const hourSegment = 3600;
class controller{
	public $bootTime;
	public $poolUnit,$databaseUnit;
	public $globalStatus = false;
	public function controller(){
		$this->poolUnit = new pool();
		$this->databaseUnit = new database();
		$this->bootTime = time()/daySegment*daySegment;
	}
	public function clear(){
		$this->poolUnit->clear();
	}
	public function setStatus(){
		$timeNow = time() % $daySegment;
		$timeSegment = $timeNow-$bootDate;
		if ($timeSegment > 9*hourSegment+30 && $timeSegment < 11*hourSegment+30){
			$this->globalStatus = true;
			return;
		}
		if ($timeSegment > 13*hourSegment && $timeSegment < 15*hourSegment){
			$this->globalStatus = true;
			return;
		}
		if ($timeSegment > 16*hourSegment && $this->globalStatus) $this->clear();
		$this->globalStatus = false;
	}
	public function process($ins){
		$this->setStatus();
		if ($this->globalStatus){
			if ($ins->getStatus() == 0 || $ins->getStatus() == 1){
				if (!$this->databaseUnit->addHistory($ins)){
					echo "Database error.\n";
					return false;
				}
				$result = $this->pool->addIns($ins);
				for ($i = 0; $i < $result->num; $i++){
					if (!$this->database->addDealing($ins)){
						echo "Database error.\n";
						return false;
					}
					if (!$this->database->changeCapital($result->ins[$i])){
						echo "Database error.\n";
						return false;
					}
				}
			}//buy or sell
			if ($ins->getStatus() == 2){
				if (!$databaseUnit->deleteHistory($ins->getID())){
					echo "Database error.\n";
					return false;
				}
				$this->pool->delIns($ins);
			}
		}
		return true;
	}
	public function close(){
		$this->clear();
	}
}

