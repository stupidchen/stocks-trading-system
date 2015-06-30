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
	public $globalStatus;
	public function controller(){
		$this->poolUnit = new pool();
		$this->databaseUnit = new database();
		$this->bootTime = time()/daySegment*daySegment;
		$this->globalStatus = false;
		clearLog();
	}
	public function clear(){
		$this->poolUnit->clear();
	}
	public function setStatus($status = NULL){
		if ($status != NULL){
			$this->globalStatus = $status;
			return;
		}
		$timeNow = time() % daySegment;
		$timeSegment = $timeNow-$this->bootTime;
		if ($timeSegment > 9*hourSegment+30 && $timeSegment < 11*hourSegment+30){
			$this->globalStatus = true;
			return;
		}
		if ($timeSegment > 13*hourSegment && $timeSegment < 15*hourSegment){
			$this->globalStatus = true;
			return;
		}
		if (($timeSegment > 16*hourSegment) && $this->globalStatus) $this->clear();
		$this->globalStatus = false;
	}
	public function process($ins){
		addLog("Controller:Processing...");
		$this->setStatus(true);
		if ($this->globalStatus) addLog("Controller:Pool is opened");
		else addLog("Controller:Pool is closed");
		if ($this->globalStatus){
			if ($ins->getStatus() == 0 || $ins->getStatus() == 1){
				if (!$this->databaseUnit->addHistory($ins)){
					addLog('Controller:Database[history] error.');
					return false;
				}
				addLog('Controller:Adding instruction to pool...');
				$result = $this->poolUnit->addIns($ins);
				addLog("Controller:$result->num trades matching. ");
				for ($i = 0; $i < $result->num; $i++){
					if (!$this->databaseUnit->addDealing($result->ins[$i])){
						addLog('Controller:Database[Deal] error.');
						return false;
					}
					if (!$this->databaseUnit->changeCapital($result->ins[$i])){
						addLog('Controller:Database[Capital] error.');
						return false;
					}
				}
			}//buy or sell
			if ($ins->getStatus() == 2){
				if (!$this->databaseUnit->deleteHistory($ins->id)){
					addLog("Controller:Database error.");
					return false;
				}
				addLog('Controller:Deleting instruction to pool...');
				if (!$this->poolUnit->deleteIns($ins)){ 
					addLog("Controller:Delete instruction failed.");
					return false;
				}
				else{
					addLog("Controller:Delete succeed!");
					return true;
				}
			}
			if ($ins->getStatus() == 3 || $ins->getStatus() == 4){
				return $this->poolUnit->changeStatus($ins);
			}
		}
		return true;
	}
	public function close(){
		$this->clear();
	}
}
//$temp = new instructions();
//$testController = new controller();
//$testController->process($temp);
