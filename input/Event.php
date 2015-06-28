<?php
use \GatewayWorker\Lib\Gateway;
use \GatewayWorker\Lib\Db;
require("./shm_def.php");//加载共享内存创建的相关参数和结构

//$db=Db::instance('db');//数据库
class Event
{
    public static function onConnect($client_id)
    {
    $shmid = shmop_open($key, 'c', PERMISSION, $size);
	$shared_stuff=new shared_use_st;
    }
   public static function onMessage($client_id, $message)
   {
        $time=date("Y:m:d-H:i:s");//进入时间,精确到秒
        $message_data = json_decode($message, true);//解码

        //数据传入格式：
        //买卖指令：type,id,aid,code,amount,price
        //重启，暂停:type,code
        //启动，关闭:type
        //撤销：type,id
        //只有type必须要有
         
        //指令合法性检查
        if(!$message_data) { Gateway::closeCurrentClient(); }//数据为空
        $keys=array_keys($message_data);//获取键名
        if($keys[0]!='type'){Gateway::sendToCurrentClient("指令格式错误\n");Gateway::closeCurrentClient(); }//参数错误
        switch($message_data['type']){
          case "IN":
          case "OUT":if($keys!=array('type','id','aid','code','amount','price'))
                  {Gateway::sendToCurrentClient("指令不合法\n");Gateway::closeCurrentClient(); }//检查键名
                   if($message_data['id']<=0||$message_data['code']<=0||$message_data['amount']<=0||$message_data['price']<=0){return;}//值检查
                   $msg=array('type'=>$message_data['type'],
                              'id'=>$message_data['id'],
                              'aid'=>$message_data['aid'],
                              'code'=>$message_data['code'],
                              'amount'=>$message_data['amount'],
                              'price'=>$message_data['price'],
                              'time'=>$time,
                             )else{
                 Gateway::sendToCurrentClient("指令不合法\n");Gateway::closeCurrentClient(); 
                           }//获取传递数组
                   break;
          case "REVOKE":if($keys[1]=='id'&&$message_data['id']>0){
                          $msg=array('type'=>$message_data['type'],
                              'id'=>$message_data['id'],
                              'aid'=>0,
                              'code'=>0,
                              'amount'=>0,
                              'price'=>0,
                              'time'=>$time,
                           );//获取数组
                        }else{Gateway::sendToCurrentClient("指令不合法\n");Gateway::closeCurrentClient(); }break;//撤销
          case "RESTART"://重启&&暂停
          case "PAUSE":if($keys[1]=='code'&&$message_data['code']>0){
                        $msg=array('type'=>$message_data['type'],
                              'id'=>0,
                              'aid'=>0,
                              'code'=>$message_data['code'],
                              'amount'=>0,
                              'price'=>0,
                              'time'=>$time,
                           );//获取数组
                       }else{Gateway::sendToCurrentClient("指令不合法\n");Gateway::closeCurrentClient(); }
                       break;
          case "START"://开启&&关闭
          case "STOP":$msg=array('type'=>$message_data['type'],
                                 'id'=>0,
                                 'aid'=>0,
                                 'code'=>0,
                                 'amount'=>0,
                                 'price'=>0,
                                 'time'=>$time,
                           );//获取数组
                      break;
          default:Gateway::sendToCurrentClient("指令不合法\n");Gateway::closeCurrentClient();break;//类型错误
        }

        // $ip=$_SERVER['REMOTE_ADDR'];客户端地址
        //$port=$_SERVER['REMOTE_PORT'];客户端端口

        if($msg['type']=='IN'||$msg['type']=='OUT'){
         $raise=Db::instance('db')->single("SELECT max_ralse FROM 'Stock' WHERE code={$msg['code']}");
         $fall=Db::instance('db')->single("SELECT max_fall FROM 'Stock' WHERE code={$msg['code']}");
         $price=Db::instance('db')->single("SELECT price FROM 'Stock_Current' WHERE code={$msg['code']}");
         if(($msg['price']<=($raise*$price/100)) && ($msg['price']>=($fall*$price/100))){
         //存入
         }
         else{ Gateway::sendToCurrentClient("指令价格错误\n");Gateway::closeCurrentClient();}
         }//涨跌停保护及存储买/卖指令
		while($shared_stuff->written!=0)
		{//written==0表示已经被读取或者还未被写入
		}//直到共享内存被控制器读取了才能进行下一步写入
        $shared_stuff->written=1;
		$shared_stuff->instruction=$msg;
		shmop_write($shmid, $shared_stuff, 0);
		//传递给控制器
        set_time_limit(0);
       $con_host="127.0.0.1";//目前用本地计算机
       $con_port=4000;//控制器端口和ip
       $socket=socket_create(AF_INET,SOCK_STREAM,0);
       $conn=socket_connect($socket,$con_host,$con_port);
        $data=json_encode($msg);
       socket_write($socket,$data,strlen($data));
        socket_close($socket);
        Gateway::sendToCurrentClient("指令接收成功\n");
   }
   
   /**
    * 当用户断开连接时
    */
   public static function onClose($client_id)
   {
	   while($shared_stuff->written!=0){};//等待最后一条指令被控制器读取
	   shmop_delete($shmid);
	   shmop_close($shmid);
   }
}
