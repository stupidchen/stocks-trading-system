<?php
use \GatewayWorker\Lib\Gateway;
use \GatewayWorker\Lib\Db;
$db=Db::instance('db');//数据库
class Event
{
   public static function onMessage($client_id, $message)
   {
        $time=date("Y:m:d H:i:s");//进入时间,精确到秒
        $message_data = json_decode($message, true);//解码

        //数据传入格式：
        //买卖指令：aid,type,code,amount,price
        //重启，暂停:type,code
        //启动，关闭:type
        //撤销：type,id,price
        //只有type必须要有
         
        //指令合法性检查
        if(!$message_data) {return;}//数据为空
        $keys=array_keys($message_data);//获取键名
        if($keys[0]!='type'&&$keys[1]!='type'){Gateway::sendToCurrentClient("Instruction format error!\n");return; }//参数错误
        switch($message_data['type']){
          case "IN"://买卖指令
          case "OUT":if($keys!=array('aid','type','code','amount','price'))
                  {Gateway::sendToCurrentClient("Instruction invalid!\n");Gateway::closeCurrentClient();return; }//检查键名
                   if($message_data['aid']>0&&$message_data['code']>0&&$message_data['amount']>0&&$message_data['price']>0){//值检查
                   $msg=array('type'=>$message_data['type'],
                              'id'=>0,
                              'aid'=>$message_data['aid'],
                              'code'=>$message_data['code'],
                              'amount'=>$message_data['amount'],
                              'price'=>$message_data['price'],
                              'time'=>$time,
                             )}else{
                 Gateway::sendToCurrentClient("Instruction invalid!\n");return; 
                           }//获取传递数组
                   break;
          case "REVOKE":if($keys[1]=='id'&&$message_data['id']>0&&$keys[2]=='price'&&$message_data['price']>0){
                          $msg=array('type'=>$message_data['type'],
                              'id'=>$message_data['id'],
                              'aid'=>0,
                              'code'=>0,
                              'amount'=>0,
                              'price'=>$message_data['price'],
                              'time'=>$message_data['time'],
                           );//获取数组
                        }else{Gateway::sendToCurrentClient("Instruction invalid!\n");return; }break;//撤销
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
                       }else{Gateway::sendToCurrentClient("Instruction invalid!\n");return; }
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
          default:Gateway::sendToCurrentClient("Instruction invalid!\n");return;break;//类型错误
        }

        // $ip=$_SERVER['REMOTE_ADDR'];客户端地址
        //$port=$_SERVER['REMOTE_PORT'];客户端端口

        if($msg['type']=='IN'||$msg['type']=='OUT'){
         $raise=Db::instance('db')->single("SELECT max_ralse FROM 'Stock' WHERE code={$msg['code']}");
         $fall=Db::instance('db')->single("SELECT max_fall FROM 'Stock' WHERE code={$msg['code']}");
         $price=Db::instance('db')->single("SELECT price FROM 'Stock_Current' WHERE code={$msg['code']}");
         if(($msg['price']>($raise*$price/100)) && ($msg['price']<($fall*$price/100)))
		{ Gateway::sendToCurrentClient("指令价格错误\n");return;}//涨跌停保护及存储买/卖指令

        //传递给控制器
        set_time_limit(0);
       $con_host="127.0.0.1";//目前用本地计算机
       $con_port=4000;//控制器端口和ip
       try{ 
          $socket=socket_create(AF_INET,SOCK_STREAM,0);
          $conn=socket_connect($socket,$con_host,$con_port);
          $data=json_encode($msg);
          socket_write($socket,$data,strlen($data));
          socket_close($socket);}
          catch(Exception $e){
         Gateway::sendToCurrentClient("Socket error, the instruction can't send to the server\n");return;
        
         }
        Gateway::sendToCurrentClient("Instruction sended!\n");
   }
   
   /**
    * 当用户断开连接时
    */
   public static function onClose($client_id)
   {
   }
}
