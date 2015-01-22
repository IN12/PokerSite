var updater;
var cards = [];
var players = [];
var main_table;
var stage = 0;
//var timer = 0;

function jsInit()
{
	updater = new EventSource('../modules/update.php');
	updater.addEventListener('message', jsUpdate, false);
	main_table = document.getElementById('table');
	
	document.getElementById('confirm').addEventListener('click', jsAction, false);
	document.getElementById('quit').addEventListener('click', jsQuit, false);
	
	jsInitCards();
	jsInitPlayerInfo();
	jsInitSubfields();
}

function jsUpdate(event)
{
	var data = JSON.parse(event.data);
	//var d = new Date(event.lastEventId * 1e3);
	//var timeStr = [d.getHours(), d.getMinutes(), d.getSeconds()].join(':');
	
	/*handle messages and their types*/
	switch(data.type)
	{
		case -1: //quit
			updater.close();
			document.getElementById('info').innerHTML="GAME QUIT.<br>Refresh to rejoin.";
			break;
		case 0: //message
			/*document.getElementById('test2').innerHTML=event.lastEventId;
			document.getElementById('test2').innerHTML+='<br>';
			document.getElementById('test2').innerHTML+=data.type;
			document.getElementById('test2').innerHTML+='<br>'+event.data;*/
			document.getElementById('test').innerHTML="";
			for (i=0; i < data.message.length; i+=1)
			{
				document.getElementById('test').innerHTML+=data.message[i].sid + " " + data.message[i].lastupdate + " " + data.message[i].ip + "<br>";
			}
			break;
		case 1: //renew session
			jsRenewSession();
			break;
		case 2:
			jsGameUpdate(data.message);
			console.log(event.data);
			console.log(data.message.stage);
			/*//clear cards
			var it;
			for (it in cards)
			{
				cards[it].parentNode.removeChild(cards[it]);
			}
			cards = [];
			//refill array
			for (i=0; i < data.message.length; i+=1)
			{
				jsAddCard(50*(i+1)+'px',50*(i+1)+'px',data.message[i].frontImage,0,i);
			}*/
			break;
		case 3:
			console.log(event.data);
			jsInfoUpdate(data.message.playerinfo);
			jsPotInfoUpdate(data.message.potinfo);
			break;
		case 4:
			jsGameUpdate(data.message);
			break;
	}
		
	//document.getElementById('table').innerHTML=event.data;
}

function jsRenewSession()
{
	$.post('../modules/renewSession.php');
}

function jsPotInfoUpdate(message)
{
	document.getElementsByClassName('potInfo')[0].innerHTML = 'Pot: '+message.pot+'. Current bet: '+message.currentbet+'.';
}

function jsInfoUpdate(message)
{
	var len = message.length;
	for (var i = 0; i < len; i+=1)
	{
		var id = message[i].id;
		
		players[id].playerFunds.innerHTML = 'Funds: ' + (message[i].funds-message[i].bet);
		if (message[i].bet>=0)
		{
			players[id].playerBet.innerHTML = 'Player\'s bet: ' + message[i].bet;
			if (message[i].data.confirmed)
			{
				switch(message[i].data.action)
				{
					case 0:
						players[id].playerAction.innerHTML = 'Planning to call...';
						break;
					case 1:
						players[id].playerAction.innerHTML = 'Planning to raise by '+message[i].data.raise;
						break;
					case 2:
						players[id].playerAction.innerHTML = 'Planning to fold.';
				}
			}
			else
			{
				players[id].playerAction.innerHTML = '';
				if (stage==2)
				{
					players[id].playerAction.innerHTML = 'Some kind of turn taking is happening';
				}
			}
		}
		else
		{
			players[id].playerBet.innerHTML = 'Player\'s bet: -';
			players[id].playerAction.innerHTML = 'Folded.';
			if (parseInt(message[i].quit))
			{
				players[id].playerAction.innerHTML = 'Folded and disconnecting.';
			}
		}
	}
}

function jsGameUpdate(message)
{
	var infotab = document.getElementById('info');
	stage = message.stage;
	switch(message.stage)
	{
		case 0:
			break;
		case 1:
			//reset all cards
			jsHideAllCards();
			
			//draw player cards
			var owner = message.owner;
			players[owner].playerId.style.color="#059905";
			jsSetCard(owner,0,message.hand[0].frontImage,1);
			jsSetCard(owner,1,message.hand[1].frontImage,1);
			
			//draw the other player cards
			var playercount = message.players.length;
			for (var i=0; i < playercount; i+=1)
			{
				var id = parseInt(message.players[i].id)
				if (id!=owner)
				{
					jsSetCard(id,0,'back.png',1);
					jsSetCard(id,1,'back.png',1);
				}
			}
			break;
		case 2:
			if (message.reactionid > -1)
			{
				infotab.getElementsByTagName('p')[0].innerHTML = 'Player '+message.rotationid+'\s raise.';
				infotab.getElementsByTagName('p')[1].innerHTML = 'Player '+message.reactionid+'\s reaction.';
			}
			else
			{
				infotab.getElementsByTagName('p')[0].innerHTML = 'Player '+message.rotationid+'\s turn.';
				infotab.getElementsByTagName('p')[1].innerHTML = '';
			}
			break;
		case 3:
		case 5:
			var len = message.dealercards.length;
			//tempcleanup
			infotab.getElementsByTagName('p')[0].innerHTML = '';
			infotab.getElementsByTagName('p')[1].innerHTML = '';
				
			for (var i=0; i < len; i+=1)
			{
				jsSetCard(0,i,message.dealercards[i].frontImage,1);
			}
			break;
		case 7:
			var len = message.dealercards.length;
			for (var i=0; i < len; i+=1)
			{
				jsSetCard(0,i,message.dealercards[i].frontImage,1);
			}
			break;
		case 8:
			break;
		case 9:
			//showdown
			var playercount = message.hands.length;
			for (var i=0; i < playercount; i+=1)
			{
				jsSetCard(message.hands[i].id,0,message.hands[i].hand[0].frontImage,1);
				jsSetCard(message.hands[i].id,1,message.hands[i].hand[1].frontImage,1);
			}
			//display results
			var resultcount = message.results.length;
			for (var i=0; i < resultcount; i+=1)
			{
				players[message.results[i].id].playerHand.innerHTML=message.results[i].eval.score+'<br>'+message.results[i].eval.note;
			}
			break;
		default:
			break;
	}
	infotab.getElementsByTagName('p')[3].innerHTML = 'Stage: '+stage;
}

function jsAction(event)
{
	var action = 0;
	var raise = 0;
	
	var selection = document.getElementsByName('action');
	var len = selection.length;
	for (var i = 0; i < len; i+=1)
	{
		if (selection[i].checked)
		{
			action = selection[i].value;
			break;
		}
	}
	raise = parseInt(document.getElementById('input-raise').value);

	$.ajax({
		type: 'POST',
		url: 'modules/action.php',
		data: {action: action, raise: raise},
		success: function(data) 
			{
				document.getElementById('status').innerHTML=data;
			}
		})
}

function jsQuit()
{
	$.ajax({
		type: 'POST',
		url: 'modules/quit.php',
		success: function(data) 
			{
				document.getElementById('quit').value="Quit: "+data;
			}
		})
}

function jsAddCard(x,y,owner,id)
{
	if (cards.length-1<=owner)
	{
		cards.push([]);
	}
	
	if (cards[owner].length-1<=id)
	{
		cards[owner].push()
	}
	
	cards[owner][id] = document.createElement('div');
	var card = cards[owner][id];
	card.className = "card";
	card.style.left = x+'px';
	card.style.top = y+'px';
	card.style.display = "none";
	//card.style.backgroundImage = "url('images/cards_small/back.png')";
	main_table.appendChild(card);
}

function jsAddInfoField(x,y,owner)
{
	if (players.length-1<=owner)
	{
		players.push([]);
	}
	
	/*if (players[owner].length-1<=id)
	{
		players[owner].push()
	}*/
	
	players[owner] = document.createElement('div');
	var field = players[owner];
	field.className = "player-info-field";
	field.style.left = x+'px';
	field.style.top = y+'px';
	//field.style.display = "none";
	//field.style.backgroundImage = "url('images/players_small/back.png')";
	field.style.width = '125px';
	//field.style.height = '50px';
	if (owner==0)
	{
		field.style.height = '28px';
		field.style.width = '280px';
	}
	main_table.appendChild(field);
}

function jsAddInfoSubfield(owner, classname, height)
{
	players[owner][classname] = document.createElement('div');
	var subfield = players[owner][classname];
	subfield.className = classname + " subfield";
	subfield.style.height = height;
	
	players[owner].appendChild(subfield);
}

function jsSetCard(owner,id,image,show)
{
	if (show==1)
	{
		cards[owner][id].style.display = "initial";
	}
	else
	{
		cards[owner][id].style.display = "none";
	}
	cards[owner][id].style.backgroundImage = "url('images/cards_small/"+image+"')";
}

function jsHideAllCards()
{
	for (var i=0; i < cards.length; i+=1)
	{
		for (var j=0; j < cards[i].length; j+=1)
		{
			jsSetCard(i,j,'back.png',0)
		}
	}
	
	//also reset other things
	for (var j=1; j < 7; j+=1)
	{
		players[j].playerId.style.color=""; //playerid colors
		players[j].playerAction.innerHTML=""; //playeraction results
		players[j].playerHand.innerHTML=""; //playerhand results
	}
}

function jsInitCards()
{
	//dealer
	for (var i=0; i < 5; i+=1)
	{
		jsAddCard(380+i*25,50,0,i);
	}
	//players1
	for (var i=0; i < 2; i+=1)
	{
		jsAddCard(30+i*25,220,1,i);
	}
	//players2
	//players3
	//players4
	//players5
	for (var j=0; j < 4; j+=1)
	for (var i=0; i < 2; i+=1)
	{
		jsAddCard(185+j*155+i*25,244,j+2,i);
	}
	//players6
	for (var i=0; i < 2; i+=1)
	{
		jsAddCard(805+i*25,220,6,i);
	}
}

function jsInitPlayerInfo()
{
	//dealer
		jsAddInfoField(340,204,0);
	//players1
		jsAddInfoField(30,380,1);
	//players2
	//players3
	//players4
	//players5
	for (var j=0; j < 4; j+=1)
		jsAddInfoField(185+j*155,404,j+2);
	//players6
		jsAddInfoField(805,380,6);
}

function jsInitSubfields()
{
	jsAddInfoSubfield(0, 'potInfo', '18px');
	//playerid display
	for (var j=1; j < 7; j+=1)
	{
		jsAddInfoSubfield(j, 'playerId', '18px');
		players[j].playerId.innerHTML='Player '+j;
	}
	//player name
	for (var j=1; j < 7; j+=1)
	{
		jsAddInfoSubfield(j, 'playerName', '18px');
		players[j].playerName.innerHTML='-';
	}
	//player funds
	for (var j=1; j < 7; j+=1)
	{
		jsAddInfoSubfield(j, 'playerFunds', '18px');
		players[j].playerFunds.innerHTML='-';
	}
	//player bet
	for (var j=1; j < 7; j+=1)
	{
		jsAddInfoSubfield(j, 'playerBet', '18px');
		players[j].playerBet.innerHTML='-';
	}
	//player actions
	for (var j=1; j < 7; j+=1)
	{
		jsAddInfoSubfield(j, 'playerAction', '36px');
		players[j].playerAction.innerHTML='-';
	}
	//player end combo
	for (var j=1; j < 7; j+=1)
	{
		jsAddInfoSubfield(j, 'playerHand', '54px');
		players[j].playerHand.innerHTML='-';
	}
}