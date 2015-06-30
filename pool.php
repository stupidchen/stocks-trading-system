<?php
function addLog($msg, $enable = true){
	$file = 'system.log';
	$time = date("[Y-m-d h:i:s] ",time());
	file_put_contents($file,$time.$msg."\n", FILE_APPEND);
}
function clearLog(){
	$file = 'system.log';
	file_put_contents($file, "");
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
	public function instructions($time = NULL, $id = NULL, $aid = NULL, $code = NULL, $amount = NULL, $price = NULL, $status = NULL, $total = NULL, $msec = NULL){
		$this->time = $time;
		$this->id = $id;
		$this->aid = $aid;
		$this->code = $code;
		$this->amount = $amount;
		$this->price = (float)$price;
		$this->status = $status;
		if ($msec == NULL) $this->msec = getTimeStamp();
		else $this->msec = $msec;
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
	public function compare($another = NULL, $status){ //this>data?true:false
		if ($this->status == 0){
			if ($this->price > $another->price) return true;
			if ($this->price < $another->price) return false;
			if ($this->msec < $another->msec) return true;
			return false;
		}
		else{		
			if ($this->price < $another->price) return true;
			if ($this->price > $another->price) return false;
			if ($this->msec < $another->msec) return true;
			return false;

		}
	}
	public function display(){
		addLog("time:$this->time, id:$this->id, aid:$this->aid, code:$this->code, amount:$this->amount, price:$this->price, status:$this->status, msec:$this->msec");
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
	public function display($number = NULL){
		$data = $this->getData();
		addLog("$number: time:$data->time, id:$data->id, aid:$data->aid, code:$data->code, amount:$data->amount, price:$data->price, status:$data->status, msec:$data->msec  level:$this->level");
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
		addLog("PoolUnit:Adding Result");
		$time = date("Y-m-d h:i:s");
		$id = $ins->id;
		$aid = $ins->aid;
		$code = $ins->code;
		$status = $ins->status;
		$this->ins[$this->num] = new instructions($time, $id, $aid, $code, $amount, $price, $status, $total);
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
		$temp = $node->right;
		$node->right = $temp->left;
		$temp->left = $node;
		$node = $temp;
	}
	public function right_rotate(&$node){
		$temp = $node->left;
		$node->left = $temp->right;
		$temp->right = $node;
		$node = $temp;
	}
	public function addBuyIns(&$node, $info){
		if ($node == NULL){
			$node = $info;
			$this->nodeNum++;
			addLog("Treap[buy]:$this->nodeNum node add finished. ");
			return;
		}
		if (!$node->compare($info, 0)){
			addLog("Treap[buy]:add a node to the left subtree. ");
			$this->addBuyIns($node->left, $info);
			if ($node->level > $node->left->level) $this->right_rotate($node);
		}
		else{
			addLog("Treap[buy]:add a node to the right subtree. ");
			$this->addBuyIns($node->right, $info);
			if ($node->level > $node->right->level) $this->left_rotate($node);
		}
	}
	public function addSellIns(&$node, $info){
		if ($node == NULL){
			$node = $info;
			$this->nodeNum++;
			addLog("Treap[sell]:$this->nodeNum node add finished. ");
			return;
		}
		if (!$node->compare($info, 1)){
			addLog("Treap[sell]:add a node to the left subtree. ");
			$this->addSellIns($node->left, $info);
			if ($node->level > $node->left->level) $this->right_rotate($node);
		}
		else{
			addLog("Treap[sell]:add a node to the right subtree. ");
			$this->addSellIns($node->right, $info);
			if ($node->level > $node->right->level) $this->left_rotate($node);
		}
	}
	public function deleteBuyIns(&$node, $info){
		if ($node == NULL) return false;
		$temp1=$node->getData()->id;
		$temp2=$info->id;
		addLog("Treap[delete]:$temp1,  $temp2");
		if ($node->getData()->id == $info->id){
			addLog("Treap[delete]:Instruction found.");
			$temp = NULL;
			$this->nodeNum--;
			if ($node->left !=  NULL) $temp = $node->left;
			if ($node->right != NULL && ($temp == NULL || $node->right->getData()->level < $temp->getData()->level)) $temp = $node->right;

			if ($temp == NULL){
				$node = NULL;
				return true;
			} 
			if ($temp == $node->left){
				$this->right_rotate($node);
				return $this->deleteBuyIns($node->right, $info);
			}
			if ($temp == $node->right){
				$this->left_rotate($node);
				return $this->deleteBuyIns($node->left, $info);
			}
		}
		else{
			if (!$node->getData()->compare($info, 0)){
				addLog("Treap[delete]:Find the instruction in the left subtree.");
				return $this->deleteBuyIns($node->left, $info);
			}
			else{
				addLog("Treap[delete]:Find the instruction in the right subtree.");
				return $this->deleteBuyIns($node->right, $info);
			}
		}
	}
	public function deleteSellIns(&$node, $info){
		if ($node == NULL) return false;
		$temp1=$node->getData()->id;
		$temp2=$info->id;
		addLog("Treap[delete]:$temp1,  $temp2");
		if ($node->getData()->id == $info->id){
			addLog("Treap[delete]:Instruction found.");
			$temp = NULL;
			$this->nodeNum--;
			if ($node->left !=  NULL) $temp = $node->left;
			if ($node->right != NULL && ($temp == NULL || $node->right->getData()->level < $temp->getData()->level)) $temp = $node->right;

			if ($temp == NULL){
				$node = NULL;
				return true;
			} 
			if ($temp == $node->left){
				$this->right_rotate($node);
				return $this->deleteSellIns($node->right, $info);
			}
			if ($temp == $node->right){
				$this->left_rotate($node);
				return $this->deleteSellIns($node->left, $info);
			}
		}
		else{
			if (!$node->getData()->compare($info, 1)){
				addLog("Treap[delete]:Find the instruction in the left subtree.");
				return $this->deleteSellIns($node->left, $info);
			}
			else{
				addLog("Treap[delete]:Find the instruction in the right subtree.");
				return $this->deleteSellIns($node->right, $info);
			}
		}
	}
	public function getFirstIns(&$node){
		if ($node->left == NULL) return $node;
		return ($this->getFirstIns($node->left));
	}
	public function changeFinishedIns(&$node){
		if ($node->left != NULL){
			if (!$this->changeFinishedIns($node->left)) return false;
		}
		if ($node->getData()->amount == 0){
			$this->nodeNum--;
			if ($node->right == NULL){
				$node=NULL;
				return true;
			}
			$node=$node->right;
			return $this->changeFinishedIns($node);
		}
		else return false;
	}
	public function matchBS(&$node, &$otherIns, &$result){ //node:Sell otherIns:Buy
		if ($node == NULL) return false;
		addLog("Pool:Matching the buy instruction to sells. ");
		if ($node->left != NULL)
			if (!$this->matchBS($node->left, $otherIns, $result)) return false;

		if ($node->getData()->price <= $otherIns->getData()->price){
			$tempAmount = getMin($node->getData()->amount, $otherIns->getData()->amount);
			$tempPrice = ($node->getData()->price + $otherIns->getData()->price)/2;
			addLog("Pool:Matching Success! amount:$tempAmount price:$tempPrice");
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
		addLog("Pool:Matching the sell instruction to buys. ");
		if ($node->left != NULL)
			if (!$this->matchSB($node->left, $otherIns, $result)) return false;

		if ($node->getData()->price >= $otherIns->getData()->price){
			$tempAmount = getMin($node->getData()->amount, $otherIns->getData()->amount);
			$tempPrice = ($node->getData()->price + $otherIns->getData()->price)/2;
			addLog("Pool:Matching Success! amount:$tempAmount price:$tempPrice");
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
	public function display($node,$number){
		if ($node != NULL){
			if ($node->left != NULL) $left = $number*2;
			else $left = -1;
			if ($node->right != NULL) $right = $number*2+1;
			else $right = -1;
			addLog("Treap[display]:  $number: $left $right");
			if ($node->left != NULL) $this->display($node->left, $number*2);
			$node->display($number);
			if ($node->right != NULL) $this->display($node->right, $number*2+1);
		}
	}
}
class stock{
	private $buyIns, $sellIns;
	private $code;
	public $status;
	public function stock($tempCode){
		$this->code = $tempCode;
		$this->buyIns = new treap();
		$this->sellIns = new treap();
		$this->status = true;
	}
	public function addBuyIns($newIns){
		addLog("PoolUnit:Add buy instruction to stock. Code:$newIns->code ");
		$newNode = new node($newIns);
		$this->buyIns->addBuyIns($this->buyIns->root,$newNode);
		$temp = $this->buyIns->getFirstIns($this->buyIns->root);
		$this->buyIns->display($this->buyIns->root,1);
		$tempData = $temp->getData();
		$result=new tradeResult();
		if ($tempData->id == $newIns->id){
			$this->sellIns->matchBS($this->sellIns->root,$temp,$result);
			if ($result->num != 0){
				$tempAmount=0;
				$tempPrice=0;	
				for ($i = 0; $i < $result->num; $i++){
					$tempAmount += $result->ins[$i]->amount;
					$tempPrice += $result->ins[$i]->total;
				}
				addLog("Pool:Matching Success! amount:$tempAmount price:$tempPrice/$tempAmount");
				$result->addResult($tempAmount, $tempPrice/$tempAmount, $newIns, $tempPrice);
				$this->sellIns->changeFinishedIns($this->sellIns->root);
				$this->buyIns->changeFinishedIns($this->buyIns->root);
			}
		}
		return $result;
	}
	public function addSellIns($newIns){
		addLog("PoolUnit:Add sell instruction to stock. Code:$newIns->code ");
		$newNode = new node($newIns);
		$this->sellIns->addSellIns($this->sellIns->root,$newNode);
		$temp = $this->buyIns->getFirstIns($this->sellIns->root);
		$this->sellIns->display($this->sellIns->root,1);
		$tempData = $temp->getData();
		$result=new tradeResult();
		if ($tempData->id == $newIns->id){
			$this->sellIns->matchSB($this->buyIns->root,$temp,$result);
			echo $this->buyIns->nodeNum.', '.$this->sellIns->nodeNum."\n";
			if ($result->num != 0){
				$tempAmount=0;
				$tempPrice=0;	
				for ($i = 0; $i < $result->num; $i++){
					$tempAmount += $result->ins[$i]->amount;
					$tempPrice += $result->ins[$i]->total;
				}
				addLog("Pool:Matching Success! amount:$tempAmount price:$tempPrice/$tempAmount");
				$result->addResult($tempAmount, $tempPrice/$tempAmount, $newIns->id, $tempPrice);
				$this->sellIns->changeFinishedIns($this->sellIns->root);
				$this->buyIns->changeFinishedIns($this->buyIns->root);
			}
			echo $this->buyIns->nodeNum.', '.$this->sellIns->nodeNum."\n";
			
		}
		return $result;
	}
	public function deleteIns($newIns){
		$result1 = $this->buyIns->deleteBuyIns($this->buyIns->root, $newIns);
		$result2 = $this->sellIns->deleteSellIns($this->SellIns->root, $newIns);
		if ($result1){
			$this->buyIns->display($this->buyIns->root,1);
			return true;
		}
		if ($result2){
			$this->SellIns->display($this->sellIns->root,1);
			return true;
		}
		return false;
	}
}
class pool{
	private $stock_ins;
	private $info;

	public function pool(){
		$this->stock_ins = array();
		$this->info = new information();
	}
	public function initStock($code){
		$this->stock_ins[$code] = new stock($code);
	}
	public function clear(){
	}
	public function addIns($tempInstruction){//status 0:buy 1:sell 2:delete
		addLog("PoolUnit:Start to add instruction.");
		$code = $tempInstruction->code;
		if ($this->stock_ins[$code] == NULL) $this->initStock($code);
		$result = new tradeResult();
		$status = $tempInstruction->status;
		if (!$this->stock_ins[$code]->status){
			addLog("PoolUnit:Add instruction failed. The stock $code may be froze");
			return $result;
		}
		$newIns = $tempInstruction;
		if ($status == 0) $result = $this->stock_ins[$code]->addBuyIns($newIns);
		else $result = $this->stock_ins[$code]->addSellIns($newIns);
		return $result;
	}
	public function deleteIns($tempInstruction){
		addLog("PoolUnit:Start to delete instruction.");
		$code = $tempInstruction->code;
		if ($this->stock_ins[$code] == NULL) $this->initStock($code);
		$newIns = $tempInstruction;
		return $this->stock_ins[$code]->deleteIns($newIns);
	}
	public function changeStatus($tempInstruction){
		$code = $tempInstruction->code;
		if ($this->stock_ins[$code] == NULL) $this->initStock($code);
		$status = $tempInstruction->status;
		addLog("PoolUnit:Change the status of stock $code. Status:$status");
		if ($status == 3){
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
