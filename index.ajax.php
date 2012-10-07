<?php
	date_default_timezone_set("UTC");
	session_start();

	include("include/config.inc.php");
	include("consolefilter/filter.inc.php");

	include("include/objects.inc.php");
	include("include/data.inc.php");

	function human_filesize($size)
	{
		$mod = 1024;
		$units = explode(' ','B KB MB GB TB PB');
		for ($i = 0; $size > $mod; $i++) {
			$size /= $mod;
		}
		return round($size, 2).$units[$i];
	}

	function recurse_rename($src,$dst)
	{
		$dir = opendir($src);
		@mkdir($dst);
		while(false !== ( $file = readdir($dir)) )
		{
			if (( $file != '.' ) && ( $file != '..' ))
			{
				if ( is_dir($src . '/' . $file) ) {
					recurse_rename($src . '/' . $file,$dst . '/' . $file);
				}
				else {
					rename($src . '/' . $file,$dst . '/' . $file);
				}
			}
		}
		closedir($dir);
	}

	$minecraft = new minecraft();

	if(isset($_GET['action']))
	{
		switch($_GET['action'])
		{
			case "console":
				$output['console'] = "";
				$console = $minecraft->console("",true);
				if(is_array($console))
					foreach($console as $line)
					{
						$opentags = 0;
						$line = scrubline($line);
						$line = htmlspecialchars($line);
						$line = cli_colour($line);

						$output['console'] .= "<div class='line'>".$line."</div>";
					}

				$output['md5'] = md5($output['console']);

				if(isset($_GET['md5']) && $_GET['md5'] == $output['md5'])
					unset($output['console']);

				echo json_encode($output);

			break;

			case "send":
				if($_GET['c'] != "")
				{
					$line = $minecraft->console($_GET['c']);

					$line = htmlspecialchars($line);
					$line = preg_replace("/^\n/","",$line);
					$line = cli_colour($line);

					echo "<div class='line new'>".$line."</div>";
				}
			break;


			case "playerop":
				$minecraft->console("op ".$_GET['p']);
			break;

			case "playerdeop":
				$minecraft->console("deop ".$_GET['p']);
			break;

			case "playerban":
				$minecraft->console("ban ".$_GET['p']);
			break;

			case "playerpardon":
				$minecraft->console("pardon ".$_GET['p']);
			break;

			case "playerkick":
				$minecraft->console("kick ".$_GET['p']);
			break;

			case "playerdelete":
				$player = new player($_GET['p']);
				$player->delete();
				unset($player);
			break;


			case "start":
				$minecraft->run();
			break;

			case "stop":
				$minecraft->stop();
			break;

			case "bounce":
				$minecraft->bounce();
			break;

			case "statearray":

				$properties = $minecraft->properties();
				unset($json_array);
				$result = $minecraft->console("list",false,true);

				if(preg_match("/\[INFO\] Connected players: (.*)/",$result,$match) == 1)
				{
					foreach(explode(",",strtolower(no_colour($match[1]))) as $playername)
					{
						$playername = explode(".",$playername);
						$playername = array_pop($playername);
						$connected[] = $playername;
					}
				}
				else
					$connected = array();

				if(isset($properties['level-name']))
				if(file_exists(config::get('MINECRAFT_PATH')."/ops.txt"))
				{
					//Get family.
					$ops = explode("\n",file_get_contents(config::get('MINECRAFT_PATH')."/ops.txt"));
					$banned = explode("\n",file_get_contents(config::get('MINECRAFT_PATH')."/banned-players.txt"));
					foreach (glob(config::get('MINECRAFT_PATH')."/".$properties['level-name']."/players/*.dat") as $filepath)
					{
						$filename = explode("/",$filepath);
						$filename = $filename[count($filename)-1];

						$playername = explode(".",$filename);
						$playername = $playername[0];
						$lastlogon = filemtime($filepath);

						$json_array['player'][$playername]['name'] = $playername;
						$json_array['player'][$playername]['logtime'] = $lastlogon;
						if(is_array($ops) && in_array(strtolower($playername),$ops)) $json_array['player'][$playername]['op'] = "true";
						if(is_array($banned) && in_array(strtolower($playername),$banned)) $json_array['player'][$playername]['banned'] = "true";
						if(is_array($connected) && in_array(strtolower($playername),$connected)) $json_array['player'][$playername]['connected'] = "true";
					}
				}

				if(file_exists(config::get('NETDEV_PATH')))
				{
					//Get system specs
					$devset1 = dev2array(file_get_contents(config::get('NETDEV_PATH')));
					sleep(1); //Wait for the values to change.
					$devset2 = dev2array(file_get_contents(config::get('NETDEV_PATH')));

					foreach($devset1 as $devname => $dev)
					{
						if(config::get('NETDEV') == $devname)
						{
							$rxbytes = $devset2[$devname]['rxbytes'] - $devset1[$devname]['rxbytes'];
							$txbytes = $devset2[$devname]['txbytes'] - $devset1[$devname]['txbytes'];

							$devpack['tx']=$txbytes;
							$devpack['rx']=$rxbytes;
							$devpack['stx']=human_filesize($txbytes);
							$devpack['srx']=human_filesize($rxbytes);
							$devpack['name']=$devname;

							$json_array['bandwidth'][]=$devpack;
						}
					}
				}

				if(file_exists(config::get('CPU_PATH')) && file_exists(config::get('LOAD_PATH')))
				{
					$cpu = cpuinfo2array(file_get_contents(config::get('CPU_PATH')));
					$load = file_get_contents(config::get('LOAD_PATH'));
					$load = explode(" ", $load );
					//Load total calulated by number processors
					$cpu_load = ($load[0]/count($cpu))*100;
					$json_array['cpu'] = $cpu_load;
				}

				$memory = exec("free -m | grep Mem");
				$memory = preg_match("/Mem:\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/",$memory,$match);
				$json_array['memory']['phys']['t'] = intval($match[1]);
				$json_array['memory']['phys']['u'] = intval($match[2] - $match[6]);

				$memory = exec("free -m | grep Swap");
				$memory = preg_match("/Swap:\s+(\d+)\s+(\d+)\s+(\d+)/",$memory,$match);
				$json_array['memory']['swp']['t'] = intval($match[1]);
				$json_array['memory']['swp']['u'] = intval($match[2]);


				//Get services
				$psout = exec("ps aux | grep ".substr(config::get('MINECRAFT_BIN'),0,strlen(config::get('MINECRAFT_BIN'))-1)."[".substr(config::get('MINECRAFT_BIN'),-1)."]");
				if($psout)
				{
					$psout_array = array();
					preg_match("/\w+\s+(\d+)\s+(\d+\.\d+)\s+(\d+\.\d+)/",$psout,$psout_array);

					if(isset($psout_array[1]))
					{
						$minecraft_pid = $psout_array[1];
						$json_array['java_cpu'] = $psout_array[2];
						$json_array['java_mem'] = $psout_array[3];
					}
					else
						$json_array['ps_res'] = $psout;
				}

				//Encode into JSON webservice.
				if(isset($minecraft_pid))
					$json_array['running'] = true;
				else
					$json_array['running'] = false;


				if(is_array($json_array)) echo json_encode($json_array);

			break;


			case "renderplayer" :

				$name = $_GET['pn']['name'];

				?>
					<div class="player <?php if(isset($_GET['pn']['connected'])) echo'connected'; ?> " player="<?php echo $name;?>">
						<div class="skin"><img src="skin.php?name=<?php echo $name;?>"></div>
						<div class="info">
							<div><?php echo $name;?></div>
							<div class="control">
								<?php
								$menu = array();
								foreach(glob("./interface/player-*.inc.php") as $includefile) include($includefile);
								?>
								<ul>
								<?php
								foreach($menu as $label=>$item)
								{
									if(isset($item['function']))
										$function = $item['function'];
									else
										$function = "send_console";

									echo "<li><a href=\"javascript:".$function."('".$item['command']."');\">".$label."</a></li>";
								}
								?>
								</ul>
							</div>
						</div>
						<div style="clear:both;"></div>
					</div>
				<?php
			break;


		}



	}


?>
