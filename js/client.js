var updater;
var cards = [];
var players = [];
var main_table;

function jsInit()
{
	updater = new EventSource('../modules/update.php');
	updater.addEventListener('message', jsUpdate, false);
	main_table = document.getElementById('table');
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
	}
		
	//document.getElementById('table').innerHTML=event.data;
}

function jsRenewSession()
{
	$.post('../modules/renewSession.php');
}

function jsGameUpdate(message)
{
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
			break;
		case 3:
		case 5:
			var len = message.dealercards.length;
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
	card.style.width = '100px';
	card.style.height = '145px';
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
		field.style.height = '22px';
		field.style.width = '200px';
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
		jsAddInfoField(380,207,0);
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
		players[j].playerName.innerHTML='placeholdername';
	}
	//player funds
	for (var j=1; j < 7; j+=1)
	{
		jsAddInfoSubfield(j, 'playerFunds', '18px');
		players[j].playerFunds.innerHTML='placeholderfunds';
	}
	//player actions
	for (var j=1; j < 7; j+=1)
	{
		jsAddInfoSubfield(j, 'playerAction', '18px');
		players[j].playerAction.innerHTML='placeholderaction';
	}
	//player end combo
	for (var j=1; j < 7; j+=1)
	{
		jsAddInfoSubfield(j, 'playerHand', '72px');
		players[j].playerHand.innerHTML='placeholderhand';
	}
}