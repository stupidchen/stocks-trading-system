function submit_single()
{
	var code=document.getElementById("code").value;
	var amount=document.getElementById("amount").value;
	var price=document.getElementById("price").value;
	var type=document.getElementById("type").value;

    var socket=new WebSocket('ws://localhost:1935');
    socket.onopen=function(event){
        socket.send("test:"+code+"_"+amount+"_"+price+"_"+type);
    };
    socket.close();
   
    alert("success!");
}
function submit_batch()
{
	var code=document.getElementById("code").value;
	var amount=document.getElementById("amount").value;
	var price=document.getElementById("price").value;
	var type=document.getElementById("type").value;
	var socket=new WebSocket('ws://localhost:1935');
	var i=0;

    socket.onopen=function(event){
        for(i=0;i<100;i++)
	    {
          socket.send("test:"+code+"_"+amount+"_"+price+"_"+type);
	    }
    };
    socket.close();
    
    alert("success!");
}
function get_message()
{
	if(typeof(EventSource)!="undefined")
	{
		var source=new EventSource("test.php");
		source.onmessage=function(event)
		{
			document.getElementById("message").value+=event.data+"\n";
		}
	}
	else
	{
		document.getElementById("result").innerHTML="对不起，获取服务器端信息失败"
	}
}
function select_change_in()
{
	document.getElementById("type").innerHTML="IN";
}
function select_change_out()
{
	document.getElementById("type").innerHTML="OUT";
}
