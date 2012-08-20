<?php
	session_start();
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="robots" content="index,follow">
		<title>proto</title>
		<link href="style/index.css" rel="stylesheet">
		<script src="js/jquery-1.7.1.min.js" type="text/javascript"></script>
		<script src="js/index.js" type="text/javascript"></script>

	</head>
	<body>
		<div class="container">
			<div class="content">
				<div class="menu">
				<?php include("include/menu.inc.php");?>
				</div>

				<div id="console"></div>

				<div id="controls">

					<div id="system">
						<div class="status">N/A</div>
						<div >
							<input id="startserver" type="button" value="Start">
							<input id="stopserver" type="button" value="Stop">
							<input id="bounceserver" type="button" value="Restart">
						</div>

					</div>
					<div id="functions">
						<div>
							<input id="line" style="width:500px;margin-right:10px;" type="text"><input type="button" id="sendline" value="send">
						</div>
						<div style="margin-top:5px;">
							<div class="statbox">
								<div>LOAD</div>
								<div id="sys_cpu">0</div>
								<div style="clear:both;"></div>
							</div>

							<div class="statbox">
								<div>MC CPU</div>
								<div id="jav_cpu">0</div>
								<div style="clear:both;"></div>
							</div>

							<div class="statbox">
								<div>MEM</div>
								<div id="sys_mem">0</div>
								<div style="clear:both;"></div>
							</div>

							<div class="statbox">
								<div>MC MEM</div>
								<div id="jav_mem">0</div>
								<div style="clear:both;"></div>
							</div>
							<div style="clear:both;"></div>
						</div>

						<div style="margin-top:5px;">
							<div class="statbox">
								<div>TX</div>
								<div id="eth_tx">0</div>
								<div style="clear:both;"></div>
							</div>

							<div class="statbox">
								<div>RX</div>
								<div id="eth_rx">0</div>
								<div style="clear:both;"></div>
							</div>
							<div style="clear:both;"></div>
						</div>
					</div>
					<div style="clear:both;"></div>
				</div>

				<div id="players">
					<div id="player_control">
					</div>

					<div id="ops">
						<h3>Ops</h3>
						<div class="list">
						</div>
						<div style="clear:both;"></div>
					</div>
					<div id="other">
						<h3>Players</h3>
						<div class="list"></div>
						<div style="clear:both;"></div>
					</div>
					<div id="banned">
						<h3>Banned</h3>
						<div class="list"></div>
						<div style="clear:both;"></div>
					</div>
					<div style="clear:both;"></div>
				</div>

			</div>
		</div>

	</body>
</html>
