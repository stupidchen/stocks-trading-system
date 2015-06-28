<?php
function addLog($msg){
	$file = 'system.log';
	$time = date("[Y-m-d h:i:s] ",time());
	file_put_contents($file,$time.$msg."\n", FILE_APPEND);
}
function getTimeStamp(){
	$timeNow=microTime();
	list($msec,$sec)=explode(" ",$timeNow);
	return ((float)$msec + (float)$sec);
}

class information{
	public $ins_num, $ins_deal_num;
	public function information($ins_num = 0, $ins_deal_num = 0){
		$this->ins_num = $ins_num;
		$this->ins_deal_num = $ins_deal_num;
	}
}
class instructions{
	public $time, $id, $aid, $code, $amount, $price, $status, $msec, $total;
	public function instructions($time = NULL, $id = NULL, $aid = NULL, $code = NULL, $amount = NULL, $price = NULL, $status = NULL, $total = NULL){
		$this->time = $time;
		$this->id = $id;
		$this->aid = $aid;
		$this->code = $code;
		$this->amount = $amount;
		$this->price = (float)$price;
		$this->status = $status;
		$this->msec = getTimeStamp();
		if ($total != NULL) $this->total = $total;
		else $this->total = $price*$amount;
	}
	public function getStatus(){
		return $this->status;
	}
	public function setID($id){
		$this->id = $id;
	}
	public function getID(){
		return $this->id;
	}
	public function setTime($time){
		$this->time = $time;
	}
	public function compare($another = NULL){ //this>data?true:false
		if ($this->price > $another->price) return true;
		if ($this->msec < $another->msec) return true;
		return false;
	}
}
class node{
	private $data;
	public $left, $right, $level;
	public function node($data = NULL){
		$this->data = $data;
		$this->left = NULL;
		$this->right = NULL;
		$this->level = mt_rand();
	}
	public function compare($node){
		return $this->data->compare($node->data);
	}
	public function setData($data = NULL){
		$this->data = $data;
	}
	public function getData(){
		return $this->data;
	}
}
class tradeResult{
	public $ins, $num;
	public function tradeResult(){
		$this->ins = array();
		$this->num = 0;
	}
	public function addResult($amount, $price, $ins, $total = NULL){
		if ($total == NULL) $total = $amount*$price;
		$this->ins[$this->num] = new instructions(time(), $ins->id, $ins->code, $amount, $price, $ins->status, $total);
		$this->num++;
		
	}
}
function getMin($x, $y){
	if ($x > $y) return $y;
	return $x;
}
class treap{
	public $root,$nodeNum;
	public function treap(){
		$this->root = NULL;
		$this->nodeNum = 0;
	}
	public function left_rotate(&$node){
		$temp = &$node->left;
		$node->left = &$temp->right;
		$temp->right = &$node;
		$node = &$temp;
	}
	public function right_rotate(&$node){
		$temp = &$node->right;
		$node->right = &$temp->left;
		$temp->left = &$node;
		$node = &$temp;
	}
	public function addIns(&$node, $info){
		if ($node == NULL){
			$node = $info;
			$this->nodeNum++;
			return;
		}
		if ($node->compare($info)){
			$this->addIns($node->left, $info);
			if ($node->level > $node->left->level) $this->right_rotate($node);
		}
		else{
			$this->addIns($node->right, $info);
			if ($node->level > $node->right->level) $this->left_rotate($node);
		}
	}
	public function deleteIns(&$node, $info){
		if ($node == NULL) return false;
		if ($node->getData()->id == $info->id && $node->getData()->price == $info->price){
			$temp = NULL;
			if ($node->left !=  NULL) $temp = $node->left;
			if ($node->right != NULL && ($temp == NULL || $node->right->getData()->level < $temp->getData()->level)) $temp = $node->right;

			if ($temp == NULL){
				$node = NULL;
				return true;
			} 
			if ($temp == $node->left){
				$this->left_rotate($node);
				return deleteIns($node->left, $id, $price);
			}
			if ($temp == $node->right){
				$this->right_rotate($node);
				return deleteIns($node->right, $id, $price);
			}
		}
		else{
			if ($node->getData()->compare($info)) return deleteIns($node->left, $info);
			else return deleteIns($node->right, $info);
		}
	}
	public function getFirstIns($node){
		if ($node->left == NULL) return $node;
		return ($this->getFirstIns($node->left));
	}
	public function changeFinishedIns(&$node){
		if ($node->left != NULL){
			if (!$this->changeFinishedIns($node->left)) return false;
		}
		if ($node->getData()->amount == 0){
			if ($node->right == NULL){
				$node=NULL;
				$this->nodeNum--;
				return true;
			}
			$node=$node->right;
			return $this->changeFinishedIns($node);
		}
		else return false;
	}
	public function matchBS(&$node, &$otherIns, &$result){ //node:Sell otherIns:Buy
		if ($node == NULL) return false;
		if ($node->left != NULL)
			if (!$this->matchBS($node->left, $otherIns, $result)) return false;

		if ($node->getData()->price <= $otherIns->getData()->price){
			$tempAmount = getMin($node->getData()->amount, $otherIns->getData()->amount);
			$tempPrice = ($node->getData()->price + $otherIns->getData()->price)/2;
			$result->addResult($tempAmount, $tempPrice, $node->getData());

			$node->getData()->amount -= $tempAmount;
			$otherIns->getData()->amount -= $tempAmount;
			if ($otherIns->getData()->amount == 0) return false;
		}
		else return false;

		if ($node->right != NULL)
			if (!$this->matchBS($node->right, $otherIns, $result)) return false;

		return true;
	}
	public function matchSB(&$node, &$otherIns, &$result){ //node:Buy otherIns:Sell
		if ($node == NULL) return false;
		if ($node->left != NULL)
			if (!$this->matchSB($node->left, $otherIns, $result)) return false;

		if ($node->getData()->price >= $otherIns->getData()->price){
			$tempAmount = getMin($node->getData()->amount, $otherIns->getData()->amount);
			$tempPrice = ($node->getData()->price + $otherIns->getData()->price)/2;
			$result->addResult($tempAmount, $tempPrice, $node->getData());

			$node->getData()->amount -= $tempAmount;
			$otherIns->getData()->amount -= $tempAmount;
			if ($otherIns->getData()->amount == 0) return false;
		}
		else return false;

		if ($node->right != NULL)
			if (!$this->matchSB($node->right, $otherIns, $result)) return false;

		return true;
	}
}
class stock{
	private $buyIns, $sellIns;
	private $code;
	public $useful;
	public function stock($tempCode){
		$this->code = $tempCode;
		$this->buyIns = new treap();
		$this->sellIns = new treap();
		$this->useful = true;
	}
	public function addBuyIns($newIns){
		$newNode = new node($newIns);
		$this->buyIns->addIns($this->buyIns->root,$newNode);
		$temp = $this->buyIns->getFirstIns($this->buyIns->root);
		$tempData = $temp->getData();
		if ($tempData->id == $newIns->id){
			$result=new tradeResult();
			$this->sellIns->matchBS($this->sellIns->root,$temp,$result);
			if ($result->num != 0){
				$tempAmount=0;
				$tempPrice=0;	
				for ($i = 0; $i < $result->num; $i++){
					$tempAmount += $result->ins[$i]->amount;
					$tempPrice += $result->ins[$i]->total;
				}
				$result->addResult($tempAmount, (float)$tempPrice/$tempAmount, $newIns, $tempPrice);
				$this->sellIns->changeFinishedIns($this->sellIns->root);
				$this->buyIns->changeFinishedIns($this->buyIns->root);
			}
		}
		return $result;
	}
	public function addSellIns($newIns){
		$newNode = new node($newIns);
		$this->sellIns->addIns($this->sellIns->root,$newNode);
		$temp = $this->buyIns->getFirstIns($this->sellIns->root);
		$tempData = $temp->getData();
		if ($tempData->id == $newIns->id){
			$result=new tradeResult();
			$this->sellIns->matchSB($this->buyIns->root,$temp,$result);
			echo $this->buyIns->nodeNum.', '.$this->sellIns->nodeNum."\n";
			if ($result->num != 0){
				$tempAmount=0;
				$tempPrice=0;	
				for ($i = 0; $i < $result->num; $i++){
					$tempAmount += $result->amount[$i];
					$tempPrice += ($result->amount[$i])*($result->price[$i]);
				}
				$result->addResult($tempAmount, (float)$tempPrice/$tempAmount, $newIns->id, $tempPrice);
				$this->sellIns->changeFinishedIns($this->sellIns->root);
				$this->buyIns->changeFinishedIns($this->buyIns->root);
			}
			echo $this->buyIns->nodeNum.', '.$this->sellIns->nodeNum."\n";
			
		}
		return $result;
	}
	public function deleteIns($newIns){
		$result1 = $this->buyIns->deleteIns($this->buyIns->root, $newIns);
		$result2 = $this->sellIns->deleteIns($this->buyIns->root, $newIns);
		return ($result1 && $result2);
	}
}
class pool{
	private $stock_ins;
	private $info;
	private $maxCode = 10000;

	public function pool(){
		$this->stock_ins = array();
		$this->maxCode = 10000;
		for ($i = 0; $i < $this->maxCode; $i++) $this->stock_ins[$i] = new stock($i);
		$this->info = new information();
	}
	public function clear(){
		for ($i = 0; $i < $this->maxCode; $i++){
			unset($this->stock_ins[$i]);
			$this->stock_ins[$i] = new stock($i);
		}
	}
	public function addIns($tempInstruction){//status 0:buy 1:sell 2:delete
		addLog("PoolUnit:Start to add instruction.");
		$result=new tradeResult();
		if (!$this->stock_ins[$code]->useful) return $result;
		$newIns=$tempInstruction;
		if ($status == 0) $result = $this->stock_ins[$code]->addBuyIns($newIns);
		else $result = $this->stock_ins[$code]->addSellIns($newIns);
		return $result;
	}
	public function deleteIns($tempInstruction){
		addLog("PoolUnit:Start to delete instruction.");
		$newIns=$tempInstruction;
		return $this->stock_ins[$code]->deleteIns($newIns);
	}
	public function changeStatus($tempInstruction){
		if ($tempInstruction->status == 3){
			$temp = false^$this->stock_ins[$code]->status;
			$this->stock_ins[$code]->status = false;
		}
		else{
			$temp = true^$this->stock_ins[$code]->status;
			$this->stock_ins[$code]->status = true;
		}
		return $temp;
	}
}
?>
