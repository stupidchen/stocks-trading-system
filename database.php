<?php
class database { 
	$private connection;
	public function database($address, $dbName, $tableName){
		$this->connection = mysql_connect($address, $dbName, $tableName);
	}
/*	public function clear(){
		$db = $connection;
    		if(!$db) die('Database connect failed.'.mysql_error());
    		mysql_select_db("sts", $db);
		$sqlQuery = "delete * from Stock_Deal_History";
*/	}
	public function close(){
		mysql_close($this->connection);
	}
    	public function addHistory(&$ins){ 
		$time = $ins->time;
		$aid = $ins->aid;
		$type = $ins->status;
		$code = $ins->code;
		$amount = $ins->amount;
		$price = $ins->price;

    		$db = $this->connection;
    		if(!$db) return false;
    		mysql_select_db("sts", $db);

        	$Date = date("Y-m-d h:i:s", strtotime("$time"));

	    	$sql1 = "select max(iid) from Stock_Deal_History";
                $result1 = mysql_query($sql1);
                $row3 = mysql_fetch_array($result1);
                $a = 1;
                $iid = $row3['max(iid)']+$a;
		$sql2 = "insert into Stock_Deal_History(iid, aid, type, code, amount, price, dealtime ) values ($iid,  $aid, $type, $code, $amount, $price, $Date)";
                if(mysql_query($sql2)){
			$ins->id = $iid;
			return true;
		}
	        else return false;
    	}

    	public function addDealing($ins) { 
		$iid = $ins->iid;
		$time = $ins->time;
		$aid = $ins->aid;
		$type = $ins->status;
		$code = $ins->code;
		$amount = $ins->amount;
		$price = $ins->price;
    		$db = $this->connection;

    		if(!$db) return false;
    		mysql_select_db("sts", $db);

        	$Date = date("Y-m-d h:i:s", strtotime("$time"));

                $result1 = mysql_query($sql1);
                $row3 = mysql_fetch_array($result1);
                $a = 1;
                $did = $row3['max(did)']+$a;
		$sql2 = "insert into Stock_Deal(did, iid, aid, type, code, amount, price, dealtime ) values ($did, $iid, $aid, $type, $code, $amount, $price, $Date1)";
                if(mysql_query($sql2)) return true;
	        else return false;
    	}
     
     
	protected function deleteHistory($iid) { 
    		$db = $this->connection;
    		if(!$db) return false;
    		mysql_select_db("sts",$db);
	    	$sql = "select * from Stock_Deal_History where iid = $iid";
		$result = mysql_query($sql);
		$i = 1;
		while ($row = mysql_fetch_array($result))
		{	
			$i=0;
			if (!mysql_query("delete * from Stock_Deal_History where iid = $s1")) return false;
		}
		return true;
	}
    
	protected function changeCapital($ins){
		$iid = $ins->iid;
		$time = $ins->time;
		$aid = $ins->aid;
		$type = $ins->status;
		$code = $ins->code;
		$amount = $ins->amount;
		$price = $ins->price;
		$

    		$db = $this->connection;
    		if(!$db) return false;
    		mysql_select_db("sts",$db);
    		$sql = "select * from Capital_Repo where aid = $aid";
		$result = mysql_query($sql);
		if ($type == 0){
			while($row = mysql_fetch_array($result)){	
				$sql1 = "update Capital_Repo set captial=captial-($price*$amount) where aid = '$aid' ";

				if (!mysql_query($sql1)) return false;
			}
		}
		else{
			while($row = mysql_fetch_array($result)){	
				$sql1 = "update Capital_Repo set captial=captial+($price*$amount) where aid = '$aid' ";

				if (!mysql_query($sql1)) return false;
			}
		}
		return true;
	}

 }

?>
