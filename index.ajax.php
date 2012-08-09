<?php
	date_default_timezone_set("UTC");
	session_start();

	$cli_colour['black'] = '0;30';
	$cli_colour['dark_gray'] = '1;30';
	$cli_colour['blue'] = '0;34';
	$cli_colour['light_blue'] = '1;34';
	$cli_colour['green'] = '0;32';
	$cli_colour['light_green'] = '1;32';
	$cli_colour['cyan'] = '0;36';
	$cli_colour['light_cyan'] = '1;36';
	$cli_colour['red'] = '0;31';
	$cli_colour['light_red'] = '1;31';
	$cli_colour['purple'] = '0;35';
	$cli_colour['light_purple'] = '1;35';
	$cli_colour['brown'] = '0;33';
	$cli_colour['yellow'] = '1;33';
	$cli_colour['light_gray'] = '0;37';
	$cli_colour['white'] = '1;37';
	$cli_colour_code = array_flip($cli_colour);
	//	"\033[" . $this->foreground_colors[$foreground_color] . "m";

	//ob_start();
	include("config.inc.php");
	//ob_end_clean();
	include("helpers.inc.php");
	include("data.inc.php");

	$minecraft = new minecraft();

	if(isset($_GET['action']))
	{
		switch($_GET['action'])
		{
			case "console":
				$console = $minecraft->console("",true);
				foreach($console as $line)
				{
					echo "<div class='line'>".$line."</div>";
				}
			break;

			case "send":
				if($_GET['c'] != "")
				{
					$console = $minecraft->console($_GET['c']);
					echo "<div class='line new'>".$console."</div>";
				}
			break;

			case "start":
				$minecraft->run();
			break;

			case "stop":
				$minecraft->stop();
			break;

			case "statearray":

				$properties = $minecraft->properties();
				unset($json_array);

				if(isset($properties['level-name']))
				if(file_exists(MINECRAFT_PATH."/ops.txt"))
				{
					//Get family.
					$ops = explode("\n",file_get_contents(MINECRAFT_PATH."/ops.txt"));
					$banned = explode("\n",file_get_contents(MINECRAFT_PATH."/banned-players.txt"));
					foreach (glob(MINECRAFT_PATH."/".$properties['level-name']."/players/*.dat") as $filepath)
					{
						$filename = explode("/",$filepath);
						$filename = $filename[count($filename)-1];

						$playername = explode(".",$filename);
						$playername = $playername[0];
						$lastlogon = filemtime($filepath);

						$json_array['player'][$playername]['logtime'] = $lastlogon;
						if(is_array($ops) && in_array(strtolower($playername),$ops)) $json_array['player'][$playername]['op'] = "true";
						if(is_array($banned) && in_array(strtolower($playername),$banned)) $json_array['player'][$playername]['banned'] = "true";
					}
				}

				if(file_exists(NETDEV_PATH))
				{
					//Get system specs
					$devset1 = dev2array(file_get_contents(NETDEV_PATH));
					sleep(1); //Wait for the values to change.
					$devset2 = dev2array(file_get_contents(NETDEV_PATH));

					foreach($devset1 as $devname => $dev)
					{
						$rxbytes = $devset2[$devname]['rxbytes'] - $devset1[$devname]['rxbytes'];
						$txbytes = $devset2[$devname]['txbytes'] - $devset1[$devname]['txbytes'];
						$json_array['bandwidth'][$devname]['tx']=$txbytes;
						$json_array['bandwidth'][$devname]['rx']=$rxbytes;
					}
				}

				if(file_exists(CPU_PATH) && file_exists(LOAD_PATH))
				{
					$cpu = cpuinfo2array(file_get_contents(CPU_PATH));
					$load = file_get_contents(LOAD_PATH);
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
				$psout = exec("ps aux | grep ".substr(MINECRAFT_BIN,0,strlen(MINECRAFT_BIN)-1)."[".substr(MINECRAFT_BIN,-1)."]");
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

		}



	}


?>
