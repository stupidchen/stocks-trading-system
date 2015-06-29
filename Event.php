<?php
use \GatewayWorker\Lib\Gateway;
use \GatewayWorker\Lib\Db;
class Event
{
   public static function onMessage($client_id, $message){
	$time=date("Y-m-d H:i:s");//进入时间,精确到秒
//	$message_data = $message;
	$message_data = json_decode($message, true);//解码
	addLog('Gateway:Received message!');

	//数据传入格式：
	//买卖指令：aid,type,code,amount,price
	//重启，暂停:type,code
	//启动，关闭:type
	//撤销：type,id,price
	//只有type必须要有
	 
	//指令合法性检查
	if(!$message_data){
		addLog("Gateway:Message is empty!");
		return;
	}//数据为空
	$keys=array_keys($message_data);//获取键名
	if ($keys[0] != 'type'){
		Gateway::sendToCurrentClient("Instruction format error!\n");
		addLog("Gateway:Instruction format error! ");
		return; 
	}//参数错误
	addLog("Gateway:Start to dividing the instruction!");
	if($keys!=array('type','id','aid','code','amount','price','time')){
		Gateway::sendToCurrentClient("Instruction invalid!\n");
		Gateway::closeCurrentClient();
		addLog("Gateway:Instruction key error. ");
		return;
     	}//检查键名
	$msg=array( //0:buy 1:sell 2:cancel 3:pause 4:start 5:open 6:close
		'type'=>$message_data['type'],
		'id'=>$message_data['id'],
		'aid'=>$message_data['aid'],
		'code'=>$message_data['code'],
		'amount'=>$message_data['amount'],
		'price'=>$message_data['price'],
		'time'=>$time,
	);

	addLog('Gateway:Instruction format checking finished!');
	if($msg['type']==0 || $msg['type']==1){
		 $db=Db::instance('db');
		 $code=$msg['code'];
		 $raise=$db->single("select max_raise from Stock where code=$code");
		 addLog('Gateway:select max_raise success');
		 $fall=$db->single("select max_fall from Stock where code=$code");
		 $price=$db->single("select price from Stock_Current where code=$code");
		 if(($msg['price']>($raise*$price/100)) && ($msg['price']<($fall*$price/100))){ 
			Gateway::sendToCurrentClient("指令价格错误\n");
			addLog("Gateway:Instruction price invalid. ");
			return;
		}//涨跌停保护及存储买/卖指令	
		Db::close('db');
	}

	addLog('Gateway:Start sending message to worker!');
	//传递给控制器
	set_time_limit(0);
	$con_host="127.0.0.1";//目前用本地计算机
	$con_port=4000;//控制器端口和ip
	try{ 
		$socket=socket_create(AF_INET,SOCK_STREAM,0);
		$conn=socket_connect($socket,$con_host,$con_port);
		$data=json_encode($msg);
		socket_write($socket,$data,strlen($data));
		socket_close($socket);
	}
	catch(Exception $e){
		addLog("Gateway:Socket error. Instruction failed to send to worker.");
		Gateway::sendToCurrentClient("Socket error, the instruction can't send to the server\n");
		return;
	}
	addLog("Gateway:Succeed!Instruction had send to worker.");
	Gateway::sendToCurrentClient("Instruction sended!\n");
   }
   
   /**
    * 当用户断开连接时
    */
   public static function onClose($client_id)
   {
   }
}
