<?php
class database{ 
	private $connection;
	public function database(){
		$this->connection=mysql_connect('121.40.194.163:3306', 'sts', 'sts2015');
	}
	public function ping(){
		if (!mysql_ping($this->connection)){
			mysql_close($this->connection);
			$this->connection=mysql_connect('121.40.194.163:3306', 'sts', 'sts2015');
		}
		return $this->connection;
	}
/*	public function clear(){
		$db = $connection;
    		if(!$db) die('Database connect failed.'.mysql_error());
    		mysql_select_db("sts", $db);
		$sqlQuery = "delete * from Stock_Deal_History";
	}*/
    	public function addHistory(&$ins){ 
		addLog('DatabaseUnit:Connecting...');
		$time = $ins->time;
		$aid = $ins->aid;
		$type = $ins->status+1;
		$code = $ins->code;
		$amount = $ins->amount;
		$price = $ins->price;
		$msec = $ins->msec;

    		$db = $this->ping();
    		if(!$db){
			addLog('DatabaseUnit:Connect failed! '.mysql_error());
			return false;
		}
    		mysql_select_db("STS", $db);

	    	$sql1 = "select max(iid) from Stock_Deal_History";
                $result1 = mysql_query($sql1);
                $row3 = mysql_fetch_array($result1);
                $a = 1;
                $iid = $row3['max(iid)']+$a;
		$sql2 = "insert into Stock_Deal_History(iid, aid, type, code, i_amount, i_price,  receive_time, timeStamp) values ('$iid', '$aid', $type, $code, $amount, $price, '$time', $msec)";
                if(mysql_query($sql2)){
			$ins->id = $iid;
			return true;
		}
	        else{
			addLog('DatabaseUnit:Add failed! '.mysql_error()."\nQuery:$sql2");
			return false;
		}
    	}

    	public function addDealing($ins) { 
		$iid = $ins->id;
		$time = $ins->time;
		$aid = $ins->aid;
		$type = $ins->status+1;
		$code = $ins->code;
		$amount = $ins->amount;
		$price = $ins->price;

    		$db = $this->ping();
    		if(!$db){
			addLog('DatabaseUnit:Connect failed! '.mysql_error());
			return false;
		}
    		mysql_select_db("STS", $db);

	    	$sql1 = "select max(did) from Stock_Deal";
                $result1 = mysql_query($sql1);
                $row3 = mysql_fetch_array($result1);
                $a = 1;
                $didt = $row3['max(did)'];
		$did = $didt + $a;
		addLog("DatabaseUnit:Add max did: $didt did: $did");
		$sql2 = "insert into Stock_Deal(did, iid, aid, type, code, amount, price, dealtime ) values ('$did', '$iid', '$aid', $type, $code, $amount, $price, '$time')";
                if(mysql_query($sql2)){
			addLog('DatabaseUnit:Add Dealing Succeed! ');
			return true;
		}
	        else{
			addLog('DatabaseUnit:Add Dealing failed! '.mysql_error()."\nQuery:$sql2");
			return false;
		}
    	}
     
     
	public function deleteHistory($iid) { 
    		$db = $this->ping();
    		if(!$db){
			addLog('DatabaseUnit:Connect failed! '.mysql_error());
			return false;
		}
    		mysql_select_db("STS",$db);

	    	$sql = "delete from Stock_Deal_History where iid = '$iid'";
		if (!mysql_query($sql)){
			addLog('DatabaseUnit:Delete history failed! '.mysql_error()."\n");
			return false;
		}
		addLog('DatabaseUnit:Delete history succeed! ');
		return true;
	}
    
	public function changeCapital($ins){
		$iid = $ins->iid;
		$time = $ins->time;
		$aid = $ins->aid;
		$type = $ins->status+1;
		$code = $ins->code;
		$amount = $ins->amount;
		$price = $ins->price;
		$total = $ins->total;

    		$db = $this->ping();
    		if(!$db){
			addLog('DatabaseUnit:Connect failed! '.mysql_error());
			return false;
		}
    		mysql_select_db("STS",$db);
    		$sql = "select * from Capital_Repo where aid = '$aid'";
		if (!mysql_query($sql)){
			addLog("DatabaseUnit:The capital account $aid does not exist.");
			return false;
		}
		$sql2 = "select hid from Stock_Hold where aid = '$aid' and code = $code";
		$hidResult = mysql_fetch_array(mysql_query($sql2));
		$hid = $hidResult['hid'];

		if ($type == 1){
			$sql3 = "update Capital_Repo set capital=capital-$total where aid = '$aid' ";
			if (!mysql_query($sql3)){
				addLog('DatabaseUnit:Update the capital failed! '.mysql_error()."\n");
				return false;
			}
			if ($hid != NULL){
				$sql4 = "update Stock_Hold set amount=amount+$amount where hid = '$hid'";

				if (!mysql_query($sql4)){
					addLog('DatabaseUnit:Update the amount failed! '.mysql_error()."\n");
					return false;
				}
			}
			else{
				$sql4 = "select max(hid) from Stock_Hold";
				$result = mysql_query($sql4);
				$row = mysql_fetch_array($result);
				$hid = $row['max(hid)']+1;
				$sql4 =  "insert into Stock_Hold(hid,aid,code,amount,statue) values ('$hid', '$aid', $code, $amount, 'NORMAL')";
				if (!mysql_query($sql4)){
					addLog('DatabaseUnit:Update the amount failed! '.mysql_error()."\n");
					return false;
				}
					
			}
		}
		else{
			$sql3 = "update Capital_Repo set capital=capital+$total where aid = '$aid' ";
			if (!mysql_query($sql3)){
				addLog('DatabaseUnit:Update the capital failed! '.mysql_error()."\n");
				return false;
			}
			if ($hid != NULL){
				$sql4 = "update Stock_Hold set amount=amount-$amount where hid = '$hid'";
				if (!mysql_query($sql4)){
					addLog('DatabaseUnit:Update the amount failed! '.mysql_error()."\n");
					return false;
				}
				$sql4 = "select * from Stock_Hold where hid = '$hid'";
				$result = mysql_query($sql4);
				$row = mysql_fetch_array($result);
				$amount = $row['amount'];
				if ($amount == 0){
					$sql4 = "delete from Stock_Hold where hid = '$hid'";
					mysql_query($sql4);
				}
			}
			else{
				addLog('DatabaseUnit:Update the amount failed!The hold record does not exist!'.mysql_error());
				return false;
			}
		}
		addLog('DatabaseUnit:Update the capital succeed! ');
		addLog('DatabaseUnit:Update the amount succeed! ');
		return true;
	}

 }

?>
