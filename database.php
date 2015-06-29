<?php
class database{ 
	public function database(){
	}
	public function connect(){
		return mysql_connect('121.40.194.163:3306', 'sts', 'sts2015');
	}
/*	public function clear(){
		$db = $connection;
    		if(!$db) die('Database connect failed.'.mysql_error());
    		mysql_select_db("sts", $db);
		$sqlQuery = "delete * from Stock_Deal_History";
	}*/
	public function close(){
		mysql_close($this->connection);
	}
    	public function addHistory(&$ins){ 
		addLog('DatabaseUnit:Connecting...');
		$time = $ins->time;
		$aid = $ins->aid;
		$type = $ins->status+1;
		$code = $ins->code;
		$amount = $ins->amount;
		$price = $ins->price;

    		$db = $this->connect();
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
		$sql2 = "insert into Stock_Deal_History(iid, aid, type, code, i_amount, i_price,  receive_time) values ('$iid', '$aid', $type, $code, $amount, $price, '$time')";
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

    		$db = $this->connect();
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
     
     
	protected function deleteHistory($iid) { 
    		$db = $this->connect();
    		if(!$db){
			addLog('DatabaseUnit:Connect failed! '.mysql_error());
			return false;
		}
    		mysql_select_db("sts",$db);

	    	$sql = "select * from Stock_Deal_History where iid = '$iid'";
		$result = mysql_query($sql);
		$i = 1;
		while ($row = mysql_fetch_array($result))
		{	
			$i=0;
			if (!mysql_query("delete * from Stock_Deal_History where iid = '$s1'")){
				addLog('DatabaseUnit:Delete history failed! '.mysql_error()."\n");
				return false;
			}
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

    		$db = $this->connect();
    		if(!$db){
			addLog('DatabaseUnit:Connect failed! '.mysql_error());
			return false;
		}
    		mysql_select_db("sts",$db);
    		$sql = "select * from Capital_Repo where aid = '$aid'";
		$result = mysql_query($sql);
		if ($type == 1){
			while($row = mysql_fetch_array($result)){	
				$sql1 = "update Capital_Repo set capital=capital-($price*$amount) where aid = '$aid' ";

				if (!mysql_query($sql1)){
					addLog('DatabaseUnit:Update the capital failed! '.mysql_error()."\n");
					return false;
				}
			}
		}
		else{
			while($row = mysql_fetch_array($result)){	
				$sql1 = "update Capital_Repo set capital=capital+($price*$amount) where aid = '$aid' ";

				if (!mysql_query($sql1)){
					addLog('DatabaseUnit:Update the capital failed! '.mysql_error()."\n");
					return false;
				}
			}
		}
		addLog('DatabaseUnit:Update the capital succeed! ');
		return true;
	}

 }

?>
