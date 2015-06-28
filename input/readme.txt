文件内容：

1.gateway,workerman: 默认根目录下，在start.php中有相对路径

2.start.php:启动文件，包括gateway,business_worker的创建


注意：***************
start.php中的gateway地址为监听地址（客户端连接的地址）

Event.php中的地址（$con_host,$con_port）为控制器的地址

/Config/Db.php中为数据库地址
目前前两者设置为本地计算机地址127.0.0.1
***********************

3.Event.php:执行内容
4./config(临时存储设置及数据库配置)



其他：
gateway:监听连接，允许并发

worker:任务处理，以守护进程运行

Event.php中数据库操作未经测试

传输过程用json加密解密


测试：（不排除本机性能影响）

1.并发连接测试(workerman测试工具benchmark)：

(a)模拟1000个用户发送1个hello，执行1000次，最后会有几十个连接reset by peer

(b)模拟500个用户发送1个hello，执行1次，全部连接成功

(c)模拟1000个用户发送1个hello，执行1次，测试两次，分别16次和4次reset by peer

(d)模拟2000个用户发送1个hello，执行1次,几十次reset by peer

(e)模拟2000个用户发送1个hello,执行100次，错误too many open file 和reset by peer

效果：能承受几百个用户的并发访问


2.三方传输测试：模拟客户端->输入器->模拟接收端（控制器）//简单传输，未包含处理过程

能正常传输数据：（workerman中不能用die out抛出错误,会导致程序中止）

3.包含数据库的处理过程未经测试

目前是每次连接从数据库中调取，但不是每次和数据库重新连接