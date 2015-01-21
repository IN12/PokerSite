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
	//var log = document.getElementById('test');
	
	//log.innerHTML=timestamp+" "+event.data+'<br>'+log.innerHTML;
	
	var cc = $('#test').find("p").size();
	if(cc >= 10){
		$('#test').find("p").last().remove();
	}
	$('#test').prepend('<p class="bg-info">'+timestamp+' '+event.data+'</p>');
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
				if (listening)
				{
					listening=0;
					//main.removeEventListener('message', jsReceiveMessage, false);
				}
				else
				{
					listening=1;
					//main.addEventListener('message', jsReceiveMessage, false);
				}
			}
	})
}