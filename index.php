<?php
	session_start();
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="robots" content="index,follow">
		<title>proto</title>
		<link href="style.css" rel="stylesheet">
		<script src="jquery-1.7.1.min.js" type="text/javascript"></script>

		<script type="text/javascript">

			var consoletimer = 0;
			var consolerefresh = 2000;
			var statetimer = 0;
			var staterefresh = 6000;
			var serverstate = {"running":true};


			function fetch_console()
			{
				//Update less often if the server isn't running.
				if(!serverstate.running)
					factor = 4;
				else
					factor = 1;

				self.clearTimeout(consoletimer);

				$.ajax({
					url: 'index.ajax.php',
					data: {"action":"console"},
					success: function(response) {
						$('#console').html(response);
					}
				});
				consoletimer = self.setTimeout(fetch_console,consolerefresh*factor);
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
							$(".status").removeClass("stopped");
						}
						else
						{
							$(".status").html("stopped");
							$(".status").removeClass("running");
							$(".status").addClass("stopped");
						}
					}
				});

				statetimer = self.setTimeout(fetch_state,staterefresh*factor);
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
					}
				});
				consoletimer = self.setTimeout(fetch_console,consolerefresh);
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
					$.ajax({
						type: "GET",
						url: 'index.ajax.php',
						data: {"action":"start"},
						success: function(response) {
							fetch_console();
							fetch_state();
						}
					});
				});

				$("#stopserver").click(function(){
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



				fetch_console();
				fetch_state();
			});

		</script>


	</head>
	<body>
		<div class="container">
			<div class="menu">
			</div>
			<div class="content">

				<div id="console"></div>

				<div id="controls">

					<div id="system">
						<div class="status">N/A</div>
						<div >
							<input id="startserver" type="button" value="Start">
							<input id="stopserver" type="button" value="Stop">
						</div>

					</div>

					<div id="functions">
						<div>
							<input id="line" style="width:500px" type="text"><input type="button" id="sendline" value="send">
						</div>
					</div>
					<div style="clear:both;"></div>
				</div>
<?php
	//ob_start();
	include("config.inc.php");
	//ob_end_clean();
	include("helpers.inc.php");

	$minecraft = new minecraft();
?>
			</div>
		</div>

	</body>
</html>
