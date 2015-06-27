<?php
class feedback { 
    public function dealin($aid,$type,$code,$amount,$price) { 
    $db = mysql_connect("121.40.194.163","sts","sts2015");
    if(!$db)
    {
    die('连接失败！'.mysql_error());
    }
    mysql_select_db("sts", $db);

	$Date=date("Y-m-d h:i:s");
        $Date1 = date("Y-m-d h:i:s", strtotime("$Date"));

	    $sql1="select max(aid) from Stock _Deal";
                $result1=mysql_query($sql1);
                $row3=mysql_fetch_array($result1);
                $a=1;
                $s7=$row3['max(aid)']+$a;
		$sql2="insert into Stock _Deal(did, aid, type, code, amount, price, dealtime ) values ('$s7',  '$aid', '$type', '$code', '$amount', '$price', '$Date1')";
                if(mysql_query($sql2))
	        {
		    header('Location:dealsucceed.php');
	         }

	        else
	        {
		    header('Location:dealfailed.php');
	        }	
    }
     
protected function delete($iid) { 
    $db=mysql_connect("121.40.194.163","sts","sts2015");
    if(!$db)
    {
    die('连接失败！'.mysql_error());
    }
    mysql_select_db("sts",$db);
    $sql="select * from Stock _Deal_History where iid = '$iid'";
	$result=mysql_query($sql);
	$i=1;
	while($row=mysql_fetch_array($result))
	{	
		$i=0;
		mysql_query("delete from Stock _Deal_History where iid = '$s1'");
		header('Location:deletesucceed.php');
	}
if($i ==1)
	header('Location:deletefailed.php');
    }
}
    
protected function deductmoney($aid,$price,$amount) {
     $db=mysql_connect("121.40.194.163","sts","sts2015");
     if(!$db)
    {
    die('连接失败！'.mysql_error());
    }
    mysql_select_db("sts",$db);
    $sql="select * from Capital_Repo where aid = '$aid'";
	$result=mysql_query($sql);
	while($row=mysql_fetch_array($result))
	{	
		
			$sql1="update Capital_Repo set frozen=frozen-（$price*$amount） where aid = '$aid' ";
			$sql2="update Capital_Repo set currency=currency-（$price*$amount）where aid = '$aid' ";

                        if(mysql_query($sql1) && mysql_query($sql2))
	                {
		             header('Location:deductssucceed.php');
	                 }

	                else
	                {
		             header('Location:deductfailed.php');
	                 }	           
	}

 }

?>