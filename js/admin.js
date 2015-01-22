var main;
var listening = 1;

function jsInit()
{
	main = new EventSource('../modules/main.php');
	main.addEventListener('message', jsReceiveMessage, false);
}

function jsReceiveMessage(event)
{
	//var data = JSON.parse(event.data);
	var d = new Date(event.lastEventId * 1e3);
	var timestamp = [d.getHours(), d.getMinutes(), d.getSeconds()].join(':');
	
	jsLog(timestamp+' '+event.data);
	
	if (event.data=='=abort=')
	{
		main.close();
		jsLog(timestamp+' EventSource closed. Refresh to restart.');
		listening=0;
	}
}

function jsLog(text)
{
	var cc = $('#test').find("p").size();
	if(cc >= 20){
		$('#test').find("p").last().remove();
	}
	$('#test').prepend('<p class="bg-info">'+text+'</p>');
}

function jsHandbrake(input)
{
$.ajax({
		url: '/modules/handbrake.php',
		type: 'post',
		success: function(data){
				input.value='Handbrake: '+data;
			}
	})
}

function jsAbort(input)
{
$.ajax({
		url: '/modules/abort.php',
		type: 'post',
		success: function(data){
				input.value='Abort: '+data;
			}
	})
}