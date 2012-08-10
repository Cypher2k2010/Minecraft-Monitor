
var consoletimer = 0;
var consolerefresh = 2000;
var consolehash = "";
var statetimer = 0;
var staterefresh = 3000;
var serverstate = {"running":true};
var playerstate;


function fetch_console()
{
	//Update less often if the server isn't running.
	if(!serverstate.running)
		factor = 2;
	else
		factor = 1;

	self.clearTimeout(consoletimer);

	$.ajax({
		url: 'index.ajax.php',
		data: {"action":"console","md5":consolehash},
		dataType: "json",
		success: function(response) {

			consolehash = response.md5;

			if(response.console)
				$('#console').html(response.console);

			consoletimer = self.setTimeout(fetch_console,consolerefresh*factor);
		}
	});
}

function fetch_state()
{
	//Update less often if the server isn't running.
	if(!serverstate.running)
		factor = 4;
	else
		factor = 1;

	self.clearTimeout(statetimer);

	$.ajax({
		url: 'index.ajax.php',
		data: {"action":"statearray"},
		dataType: "json",
		success: function(response) {
			serverstate = response;

			if(response.running)
			{
				$(".status").html("started");
				$(".status").addClass("running");
				$(".status").removeClass("working");
				$(".status").removeClass("stopped");
			}
			else
			{
				$(".status").html("stopped");
				$(".status").removeClass("working");
				$(".status").removeClass("running");
				$(".status").addClass("stopped");
			}

			$("#sys_cpu").html(response.cpu+"%");
			$("#jav_cpu").html(response.java_cpu+"%");

			var memorypercent = (response.memory.phys.u / response.memory.phys.t)*100;
			memorypercent = Math.round(memorypercent*100)/100;

			$("#sys_mem").html(memorypercent+"%");
			$("#jav_mem").html(response.java_mem + "%");

			$("#eth_tx").html(response.bandwidth[0].stx);
			$("#eth_rx").html(response.bandwidth[0].srx);

			var playerstate_changed = false;

			if(JSON.stringify(response.player) != playerstate)
				playerstate_changed = true;


			for (i in response.player)
			{
				if($("div[player="+response.player[i].name+"].player").length == 0 || playerstate_changed)
					reload_player(response.player[i]);
			}

			playerstate = JSON.stringify(response.player);

			statetimer = self.setTimeout(fetch_state,staterefresh*factor);
		}
	});

}

function send_console(command)
{
	self.clearTimeout(consoletimer);
	$.ajax({
		type: "GET",
		url: 'index.ajax.php',
		data: {"action":"send","c":command},
		success: function(response) {
			$('#console').append(response);
			fetch_state();
			consoletimer = self.setTimeout(fetch_console,consolerefresh);
		}
	});
}

function player_ban(name)
{
	$.ajax({
		type: "GET",
		url: 'index.ajax.php',
		data: {"action":"playerban","p":name},
		success: function(response) {
			$("div[player="+name+"].player").appendTo("#banned div.list");
		}
	});
}

function player_pardon(name)
{
	$.ajax({
		type: "GET",
		url: 'index.ajax.php',
		data: {"action":"playerpardon","p":name},
		success: function(response) {
			$("div[player="+name+"].player").appendTo("#other div.list");
		}
	});
}

function player_kick(name)
{
	$.ajax({
		type: "GET",
		url: 'index.ajax.php',
		data: {"action":"playerkick","p":name},
		success: function(response) {
		}
	});
}

function player_op(name)
{
	$.ajax({
		type: "GET",
		url: 'index.ajax.php',
		data: {"action":"playerop","p":name},
		success: function(response) {
			$("div[player="+name+"].player").appendTo("#ops div.list");
		}
	});
}

function player_deop(name)
{
	$.ajax({
		type: "GET",
		url: 'index.ajax.php',
		data: {"action":"playerdeop","p":name},
		success: function(response) {
			$("div[player="+name+"].player").appendTo("#other div.list");
		}
	});
}
function player_delete(name)
{
	$.ajax({
		type: "GET",
		url: 'index.ajax.php',
		data: {"action":"playerdelete","p":name},
		success: function(response) {
			$("div[player="+name+"].player").fadeOut(500);
		}
	});
}



function reload_player(playernode)
{
	if(!playernode.name)
	{
		playernode.name = playernode;
	}

	var player_html = "";

	$.ajax({
		type: "GET",
		url: 'index.ajax.php',
		data: {"action":"renderplayer","pn":playernode},
		success: function(response) {
			player_html = response;

			if($("div[player="+playernode.name+"].player").length == 0)
			{
				if(playernode.op)
				{
					$("#ops div.list").append(player_html);
				}
				else if(playernode.banned)
				{
					$("#banned div.list").append(player_html);
				}
				else
				{
					$("#other div.list").append(player_html);
				}
			}
			else
			{
				$("div[player="+playernode.name+"].player").replaceWith(player_html);
			}

		}
	});

}


$(document).ready(function(){

	$("#sendline").click(function(){
		send_console($("#line").val());
		$("#line").select();
	});

	$('#line').keyup(function(event) {
		if (event.which == 13) {
			$("#sendline").click();
		}
	}).keydown(function(event) {
		if (event.which == 13) {
			event.preventDefault();
		}
	});


	$("#startserver").click(function(){
		$(".status").html("working");
		$(".status").addClass("working");
		$(".status").removeClass("stopped");
		$(".status").removeClass("started");
		self.clearTimeout(statetimer);

		$.ajax({
			type: "GET",
			url: 'index.ajax.php',
			data: {"action":"start"},
			success: function(response) {
				serverstate.running = false;
				fetch_console();
				fetch_state();
			}
		});
	});

	$("#stopserver").click(function(){
		$(".status").html("working");
		$(".status").addClass("working");
		$(".status").removeClass("stopped");
		$(".status").removeClass("started");
		self.clearTimeout(statetimer);

		$.ajax({
			type: "GET",
			url: 'index.ajax.php',
			data: {"action":"stop"},
			success: function(response) {
				fetch_console();
				fetch_state();
			}
		});
	});

	$("#bounceserver").click(function(){
		$(".status").html("working");
		$(".status").addClass("working");
		$(".status").removeClass("stopped");
		$(".status").removeClass("started");
		self.clearTimeout(statetimer);

		$.ajax({
			type: "GET",
			url: 'index.ajax.php',
			data: {"action":"bounce"},
			success: function(response) {
				serverstate.running = true;
				fetch_console();
				fetch_state();
			}
		});
	});

	fetch_console();
	fetch_state();
});
