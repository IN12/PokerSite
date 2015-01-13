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
	var log = document.getElementById('test');
	
	log.innerHTML=timestamp+" "+event.data+'<br>'+log.innerHTML;
}