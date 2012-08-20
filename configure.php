<?php
	session_start();
	include("include/config.inc.php");
	include("include/data.inc.php");

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="robots" content="index,follow">
		<title>proto</title>
		<link href="style/index.css" rel="stylesheet">
		<script src="js/jquery-1.7.1.min.js" type="text/javascript"></script>
		<script src="js/configure.js" type="text/javascript"></script>

	</head>
	<body>
		<div class="container">
			<div class="content">
				<div class="menu">
				<?php include("include/menu.inc.php");?>
				</div>


				<div id="config">
					<h2>Minecraft Monitor Config</h2>

					<div class="monitor pagedialog">
						<h3>Monitor Customisations</h3>

						<div class="row">
							<div class="label">WAN Interface:</div>
							<div class="control">
								<select name="eth">
								<?php
									$devset = dev2array(file_get_contents(config::get('NETDEV_PATH')));
									foreach($devset as $devname=>$devdata)
									{
										echo "<option>".$devname."</option>";
									}
								?>
								</select>
							</div>
							<div class="info">
								<p>Sometimes when a server has more than one network interface, you'll need to specify which one is the primary.</p>
								<p>This is the interface that the bandwidth measurements use.</p>
							</div>
							<div style="clear:both;"></div>
						</div>


						<div class="row">
							<div class="label">:</div>
							<div class="control">

							</div>
							<div class="info">
								<p></p>
							</div>
							<div style="clear:both;"></div>
						</div>


					</div>

					<div class="server pagedialog">
						<h3>Server</h3>
						<div class="row">
							<div class="label">Memory Allocation:</div>
							<div class="control">MIN:<input type="text" name="MIN_MEMORY" value="<?php echo config::get('MIN_MEMORY');?>" style="width: 60px;">MB  &nbsp;&nbsp;&nbsp;&nbsp; MAX:<input type="text" name="MAX_MEMORY" value="<?php echo config::get('MAX_MEMORY');?>" style="width: 60px;">MB</div>
							<div class="info">
								<p>This is the amount of memory that Java is permitted to use.</p>
								<p><b>MIN:</b> The amount of memory that Java will reserve for itself when it starts minecraft.</p>
								<p><b>MAX:</b> The amount that Java is permitted to use if it runs out of the existing allocated memory.</p>
							</div>
							<div style="clear:both;"></div>
						</div>

						<div class="row">
							<div class="label">Minecraft Location: </div>
							<div class="control"><input type="text" name="MINECRAFT_PATH" value="<?php echo config::get('MINECRAFT_PATH'); ?>" style="width: 360px;"></div>
							<div class="info">
								<p>The folder where the mincraft files are located. This is the folder with the world folders, the JAR file and the server.properties file.</p>
							</div>
							<div style="clear:both;"></div>
						</div>

						<div class="row">
							<div class="label">Minecraft JAR: </div>
							<div class="control">
								<select name="MINECRAFT_BIN">
								<?php
								foreach (glob(config::get('MINECRAFT_PATH')."/*.jar") as $filepath)
								{
									$fileseg = array_pop(explode("/",$filepath));
									if(config::get('MINECRAFT_BIN') == $fileseg)
										$selected = "selected";
									else
										$selected = "";

									echo "<option ".$selected.">".$fileseg."</option>";
								}
								?>
								</select>
							</div>
							<div class="info">
								<p>The is the main Minecraft server JAR file. If you are running a mod loader, such as BUKKIT or TEKKIT then it is the name of the modloader JAR file.</p>
							</div>
							<div style="clear:both;"></div>
						</div>
					</div>

					<div class="system pagedialog">
						<h3>System</h3>

						<div class="row">
							<div class="label">Pipe location: </div>
							<div class="control"><input type="text" name="PIPE_PATH" value="<?php echo config::get('PIPE_PATH'); ?>" style="width: 360px;"></div>
							<div class="info">
								<p>This is where the IO redirection files are stored. It is usually OK to put them in your minecraft path, however on some systems this can cause problems and you might need to move them.</p>
							</div>
							<div style="clear:both;"></div>
						</div>

						<div class="row">
							<div class="label">Network device: </div>
							<div class="control"><input type="text" name="NETDEV_PATH" value="<?php echo config::get('NETDEV_PATH'); ?>" style="width: 200px;"></div>
							<div class="info">
								<p>The location in the proc filesystem where the NET devices can be found.<br>It's recommended to leave this as default, but on some distributions of linux/unix these are in different places.</p>
							</div>
							<div style="clear:both;"></div>
						</div>

						<div class="row">
							<div class="label">CPU Information: </div>
							<div class="control"><input type="text" name="CPU_PATH" value="<?php echo config::get('CPU_PATH'); ?>" style="width: 200px;"></div>
							<div class="info">
								<p>The location in the proc filesystem where the CPU details can be found.<br>It's recommended to leave this as default.</p>
							</div>
							<div style="clear:both;"></div>
						</div>

						<div class="row">
							<div class="label">Load Averages: </div>
							<div class="control"><input type="text" name="LOAD_PATH" value="<?php echo config::get('LOAD_PATH'); ?>" style="width: 200px;"></div>
							<div class="info">
								<p>The location in the proc filesystem where the load averages can be found.<br>It's recommended to leave this as default.</p>
							</div>
							<div style="clear:both;"></div>
						</div>


					</div>

					<div class="api pagedialog">
						<h3>Api</h3>
						<div class="row">
							<div class="label">API Password: </div>
							<div class="control"><input type="text" name="API_PASSWORD" value="<?php echo config::get('API_PASSWORD'); ?>" style="width: 200px;"></div>
							<div class="info">
								<p>The unique password used to generate the API challenge hash.</p>
							</div>
							<div style="clear:both;"></div>
						</div>
						<div class="row">
							<div class="label">API Server Handle: </div>
							<div class="control"><input type="text" name="API_HANDLE" value="<?php echo config::get('API_HANDLE'); ?>" style="width: 120px;"></div>
							<div class="info">
								<p>The handle used to identify this particular server instance.</p>
							</div>
							<div style="clear:both;"></div>
						</div>
						<div class="row">
							<div class="label">Log Lines: </div>
							<div class="control"><input type="text" name="LOG_LINES" value="<?php echo config::get('LOG_LINES'); ?>" style="width: 40px;"></div>
							<div class="info">
								<p>This is for optimising communication between the API components. Keep this number small to minimise the amount of bandwidth used on updates and the latency low.</p>
							</div>
							<div style="clear:both;"></div>
						</div>
					</div>
				</div>

			</div>
		</div>

	</body>
</html>
