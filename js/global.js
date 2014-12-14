var updater;

function jsInit()
{
	updater = new EventSource('../modules/update.php');
	updater.addEventListener('message', jsUpdate, false);
}

function jsUpdate(event)
{
	var data = JSON.parse(event.data);
	var d = new Date(event.lastEventId * 1e3);
	var timeStr = [d.getHours(), d.getMinutes(), d.getSeconds()].join(':');

	document.getElementById('table').innerHTML=timeStr;
	document.getElementById('table').innerHTML+='<br>';
	document.getElementById('table').innerHTML+=data.test;
	document.getElementById('table').innerHTML+='<br>'+event.data;
	
	if (data.renewSession==1)
	{
		jsRenewSession();
	}
	
	document.getElementById('test').innerHTML="";
	for (i=0; i < data.sessions.length; i+=1)
	{
		document.getElementById('test').innerHTML+=data.sessions[i].sid + " " + data.sessions[i].lastupdate + " " + data.sessions[i].ip + "<br>";
	}
	//document.getElementById('table').innerHTML=event.data;
}

function jsRenewSession()
{
	$.post('../modules/renewSession.php');
}