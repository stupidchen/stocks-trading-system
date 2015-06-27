<?php
require("./head.php");
class information{
	public $ins_num, $ins_deal_num;
	public function information($ins_num = 0, $ins_deal_num = 0){
		$this->ins_num = $ins_num;
		$this->ins_deal_num = $ins_deal_num;
	}
}
class instructions{
	public $time, $id, $amount, $price;
	public function instructions($allIns){
		$this->time = $allIns->time;
		$this->id = $allIns->id;
		$this->amount = $allIns->amount;
		$this->price = $allIns->price;
	}
	public function compare($another = NULL){ //this>data?true:false
		if ($this->price > $another->price) return true;
		if ($this->time < $another->time) return true;
		return false;
	}
}
class allInstructions{
	public $time, $id, $code, $amount, $price, $status;
	public function allInstructions($time = NULL, $id = NULL, $code = NULL, $amount = NULL, $price = NULL, $status = NULL){
		$this->time = $time;
		$this->id = $id;
		$this->code = $code;
		$this->amount = $amount;
		$this->price = $price;
		$this->status = $status;
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
	public $amount, $price, $insID;
	public $num;
	public function tradeResult(){
		$this->insID = array();
		$this->amount = array();
		$this->price = array();
		$this->num = 0;
	}
	public function addResult($amount_0, $price_0, $insID_0){
		$this->insID[$this->num] = $insID_0;
		$this->amount[$this->num] = $amount_0;
		$this->price[$this->num] = $price_0;
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
			$result->addResult($tempAmount, $tempPrice, $node->getData()->id);

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
			$result->addResult($tempAmount, $tempPrice, $node->getData()->id);

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
					$tempAmount += $result->amount[$i];
					$tempPrice += ($result->amount[$i])*($result->price[$i]);
				}
				$tempPrice /= $tempAmount;
				$result->addResult($tempAmount, $tempPrice, $newIns->id);
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
				$tempPrice /= $tempAmount;
				$result->addResult($tempAmount, $tempPrice, $newIns->id);
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
	public function addInstruction($tempInstruction){//status 0:buy 1:sell 2:delete 3:pause 4:restart
		$result=new tradeResult();
		if (!$this->stock_ins[$code]->useful) return $result;
		$newIns=new instructions($tempInstruction);
		if ($status == 0) $result = $this->stock_ins[$code]->addBuyIns($newIns);
		else $result = $this->stock_ins[$code]->addSellIns($newIns);
		return $result;
	}
	public function deleteInstruction($tempInstruction){
		$newIns=new instructions($tempInstruction);
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
