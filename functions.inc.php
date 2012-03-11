<?php

	/*
	Mincraft Monitor Webservice object: Version 0.2
	http://www.twosphere.com.au
	http://www.minecraftmonitor.com.au
	
	All code is provided by Twosphere for testing purposes only.
	This code has not been thoroughly tested under all conditions. Twosphere, therefore, cannot guarantee or imply reliability, serviceability, or function of these programs.
	All programs contained herein are provided to you "AS IS" without any warranties of any kind.
	The implied warranties of non-infringement, merchantability and fitness for a particular purpose are expressly disclaimed.

	date: 2011-08-11
	File: functions.inc.php
	Webservice 'action' functions.
	*/	
	
	
	//Security, ignore this line
	if (!empty($_SERVER['SCRIPT_FILENAME']) && 'comm.php' != basename($_SERVER['SCRIPT_FILENAME']))die ('Loading this page directly will bring dishonor to your family.');	
	

	
	
	//General functions
	if(isset($_GET['action']) && $_GET['action'] == "config")
	{
		$json_array = json_decode($json_settings,true);

		if(isset($_GET['v1']) && isset($_GET['v2']))
		$json_array[$_GET['v1']]=$_GET['v2'];
		
		$config = file_get_contents("config.inc.php");		
		$config = str_replace($json_settings, json_encode($json_array) ,$config);
		file_put_contents("config.inc.php",$config);
		die();
	}

	if(isset($_GET['action']) && $_GET['action'] == "setproperties")
	{
		$jsondata = json_decode(base64_decode($_GET['data']),true);

		$fhandle = fopen(MINECRAFT_PATH."/server.properties","w");
		fwrite($fhandle, "#Minecraft server properties\n");
		fwrite($fhandle, "#Generated by www.minecraftmonitor.com\n");
		fwrite($fhandle, "#".date("Y-m-d H:i:s")."\n");
		foreach($jsondata as $key=>$value)
		{
			fwrite($fhandle, $key."=".$value."\n");
		}
		fclose($fhandle);
		die();
	}

	if(isset($_GET['action']) && $_GET['action'] == "install-dir")
	{
		mkdir(MINECRAFT_PATH);
		mkdir(MINECRAFT_PATH."/backup");
		minecraft_getbin();		
		die();
	}

	if(isset($_GET['action']) && $_GET['action'] == "install-java")
	{
		set_time_limit(600);

		if(file_exists("javalock")) die("installing java");
		
		touch("javalock");
		if(!file_exists(JAVA_PATH))
		{
			//Get Java from sun
			passthru("wget http://javadl.sun.com/webapps/download/AutoDL?BundleId=49015 -q -O javainstall.bin");

			//Run self extractor
			if(file_exists("javainstall.bin") && !is_executable("javainstall.bin")) chmod("javainstall.bin", 0777);
			passthru("./javainstall.bin");

			//move it to the permenant location
			$javatemp = exec("ls -1 | grep jre");		
			exec("mv ./".$javatemp."/ ".JAVA_PATH);

			//Clean up
			unlink("javainstall.bin");
			unlink("javalock");
		}
		else
			echo "already installed";
		die();
	}


	if(isset($_GET['action']) && $_GET['action'] == "survey")
	{
		if(file_exists("config.inc.php"))					$json['webfiles']['config'] = true; else	$json['webfiles']['config'] = false;
		if(file_exists("helpers.inc.php"))					$json['webfiles']['helper'] = true; else	$json['webfiles']['helper'] = false;
		if(file_exists("data.inc.php"))						$json['webfiles']['data'] = true; else		$json['webfiles']['data'] = false;
		if(file_exists("access.inc.php"))					$json['webfiles']['access'] = true; else	$json['webfiles']['access'] = false;
		if(file_exists("functions.inc.php"))				$json['webfiles']['functions'] = true; else  $json['webfiles']['functions'] = false;
		if(file_exists(MINECRAFT_PATH."/server.properties"))$json['files']['properites'] = true; else  $json['files']['properites'] = false;
		if(file_exists(MINECRAFT_PATH."/ops.txt"))			$json['files']['ops'] = true; else		$json['files']['ops'] = false;
		if(file_exists(MINECRAFT_PATH."/server.mon"))		$json['files']['log'] = true; else		$json['files']['log'] = false;
		if(file_exists("/bin/gzip"))						$json['files']['gzip'] = true; else		$json['files']['gzip'] = false;
		if(file_exists(MINECRAFT_PATH."/".MINECRAFT_BIN))	$json['files']['minecraft'] = true; else $json['files']['minecraft'] = false;
		if(file_exists(NETDEV_PATH))						$json['device']['net'] = true; else		$json['device']['net'] = false;
		if(file_exists(CPU_PATH))							$json['device']['cpu'] = true; else		$json['device']['cpu'] = false;
		if(file_exists(LOAD_PATH))							$json['device']['load'] = true; else	$json['device']['load'] = false;
		if(is_writable($_SERVER["DOCUMENT_ROOT"]))			$json['perm']['webroot'] = true; else	$json['perm']['webroot'] = false;

		$json['service']['ps'] =	exec("ps -V");
		$json['service']['gzip'] =	exec("/bin/gzip -V | grep \"gzip \"");
		$json['service']['java'] =	exec(JAVA_PATH."bin/java -version 2>&1 | grep version");
		die(json_encode($json));
	}

	if(isset($_GET['action']) && $_GET['action'] == "ping")
	{
		
		$raw = exec("ping -c 3 -w 3 ".escapeshellarg($_GET['host'])." | grep 'bytes from'");
		$ping = preg_match("/\d* bytes from (\d+\.\d+\.\d+\.\d+): icmp_seq=\d* ttl=\d* time=(\d+\.*\d*) ms/",$raw,$ping_match);

		$json['host'] = $ping_match[1];
		$json['latency'] = $ping_match[2];
		$json['text'] = $ping_match[2]." ms";

		die(json_encode($json));
	}

	if(isset($_GET['action']) && $_GET['action'] == "bounce")
	{
		minecraft_bounce();
		die();
	}

	if(isset($_GET['action']) && $_GET['action'] == "start")
	{
		if(!minecraft_running()) minecraft_run();
		die();
	}

	if(isset($_GET['action']) && $_GET['action'] == "stop")
	{
		if(minecraft_running()) minecraft_stop();
		die();
	}

	if(isset($_GET['action']) && $_GET['action'] == "command")
	{
		if(!isset($_GET['line']) && $_GET['line'] == "")
		{
			minecraft_send("say Command was empty");
		}
		else
		{
			minecraft_send(trim($_GET['line']));
		}
		die();
	}

	if(isset($_GET['action']) && $_GET['action'] == "backup")
	{
		minecraft_backupworld();
		die();
	}

	if(isset($_GET['action']) && $_GET['action'] == "update")
	{
		minecraft_updatebin();
		die();
	}

	if(isset($_GET['action']) && $_GET['action'] == "deleteplayer")
	{		
		unlink(MINECRAFT_PATH."/world/players/".ereg_replace("[^A-Za-z0-9\_]", "", $_GET['player'] ).".dat");
		die();
	}





	if(isset($_GET['player']))
	{
		if(file_exists(MINECRAFT_PATH."/server.properties"))
		{
			$settings = file_get_contents(MINECRAFT_PATH."/server.properties");
			$settings = explode("\n",$settings);
			foreach($settings as $line)
			{
				//Remove comments
				$line = preg_replace("/\#.*/","",$line);

				if(strpos($line,"=") !== false)
				{
					list($param,$value) = explode("=",$line);
					$properties[$param]=$value;
				}
			}
		}

		
		if(isset($_GET['action']) && $_GET['action'] == "lock")
		{
			if($_GET['state'] == "on")
			{
				minecraft_send("ban ".$_GET['player']);
				rename(MINECRAFT_PATH."/".$properties['level-name']."/players/".$_GET['player'].".dat",MINECRAFT_PATH."/".$properties['level-name']."/players/".$_GET['player']."_lock.dat");
			}
			elseif($_GET['state'] == "off")
			{
				minecraft_send("pardon ".$_GET['player']);
				rename(MINECRAFT_PATH."/".$properties['level-name']."/players/".$_GET['player']."_lock.dat",MINECRAFT_PATH."/".$properties['level-name']."/players/".$_GET['player'].".dat");
			}
			elseif($_GET['state'] == "check")
			{
				if(!file_exists(MINECRAFT_PATH."/".$properties['level-name']."/players/".$_GET['player']."_lock.dat"))
					echo "unlocked";
				else
					echo "locked";
			}
			die();
		}	
		
		
		if(isset($_GET['action']) && $_GET['action'] == "inventory")
		{
			if(!file_exists(MINECRAFT_PATH."/".$properties['level-name']."/players/".$_GET['player']."_lock.dat") )
				die();

			$level = new NBT();
			$level->loadFile(MINECRAFT_PATH."/".$properties['level-name']."/players/".$_GET['player']."_lock.dat");


			file_put_contents($_GET['player']."_dat",print_r($level->root,true));


			foreach($level->root[0]['value'] as $key1=>$tag)
			{
				if($tag['name'] == "Inventory")
				{
					/*
					$h = fopen("testwrite","a");
					fwrite($h,"-----------------read---------------\n");
					fwrite($h,print_r($level->root[0]['value'][$key1],true));
					fclose($h);
					*/

					foreach($tag['value']['value'] as $key2=>$conv)
					{
						$item = array();
						foreach($conv as $conveach) $item[$conveach['name']]= $conveach['value'];
						$inventory[$item['Slot']]=$item;
					}
				}

			}
			die(json_encode($inventory));
		}


		
		
		
		if(isset($_GET['action']) && $_GET['action'] == "write_inventory")
		{
			if(!file_exists(MINECRAFT_PATH."/".$properties['level-name']."/players/".$_GET['player']."_lock.dat") )
				die();

			$datafile = MINECRAFT_PATH."/".$properties['level-name']."/players/".$_GET['player']."_lock.dat";

			$level = new NBT();
			$level->loadFile($datafile);

			//Write json to inventory
			foreach($level->root[0]['value'] as $key1=>$tag)
			{
				if($tag['name'] == "Inventory")
				{
					$level->root[0]['value'][$key1]['value']['type'] = 10;
					$level->root[0]['value'][$key1]['value']['value'] = array();
					
					if(isset($_GET['data']) && $_GET['data'] != "")
					{
						$inventory_json = json_decode($_GET['data'],true);
						//Scrub inventory
						if(is_array($inventory_json))
						foreach($inventory_json as $item)
						{
							$level->root[0]['value'][$key1]['value']['value'][] = $item;
						}
					}

					/*
					$h = fopen("testwrite","a");
					fwrite($h,"-----------------write---------------\n");
					fwrite($h,print_r($level->root[0]['value'][$key1],true));
					fclose($h);
					*/


				}
			}
			$level->writeFile($datafile);

			die();
		}


		if(isset($_GET['action']) && $_GET['action'] == "inventoryslot")
		{
			if(!file_exists(MINECRAFT_PATH."/".$properties['level-name']."/players/".$_GET['player']."_lock.dat") )
				die();

			$level = new NBT();
			$level->loadFile(MINECRAFT_PATH."/".$properties['level-name']."/players/".$_GET['player']."_lock.dat");

			foreach($level->root[0]['value'] as $key1=>$tag)
			{
				if($tag['name'] == "Inventory")
				{
					foreach($tag['value']['value'] as $key2=>$conv)
					{
						$item = array();
						foreach($conv as $conveach) $item[$conveach['name']]= $conveach['value'];
						$inventory[$item['Slot']]=$item;
					}
				}

			}
			if(isset($inventory[$_GET['slot']]))
				die(json_encode($inventory[$_GET['slot']]));
			else
				die();
		}

		if(isset($_GET['action']) && $_GET['action'] == "additem")
		{
			if(!file_exists(MINECRAFT_PATH."/".$properties['level-name']."/players/".$_GET['player']."_lock.dat") )
				die();

			$datafile = MINECRAFT_PATH."/".$properties['level-name']."/players/".$_GET['player']."_lock.dat";

			$level = new NBT();
			$level->loadFile($datafile);
			//var_dump($level->root);

			$newitem = array(array("type"=>2,"name"=>"id","value"=>(int)$_GET['id']),
							 array("type"=>2,"name"=>"Damage","value"=>(int)$_GET['damage']),
							 array("type"=>1,"name"=>"Count","value"=>(int)$_GET['count']),
							 array("type"=>1,"name"=>"Slot","value"=>(int)$_GET['slot']));

				foreach($level->root[0]['value'] as $key1=>$tag)
				{
					if($tag['name'] == "Inventory")
					{
						foreach($tag['value']['value'] as $key2=>$conv)
						{
							$item = array();
							foreach($conv as $conveach) $item[$conveach['name']]= $conveach['value'];
							$inventory[$item['Slot']]=$item;
						}

						if(!isset($inventory[$_GET['slot']]))
							$level->root[0]['value'][$key1]['value']['value'][] = $newitem;
						else
							die("Slot occupied");
					}

				}
			$level->writeFile($datafile);
			die();
		}

		if(isset($_GET['action']) && $_GET['action'] == "delitem")
		{
			if(!file_exists(MINECRAFT_PATH."/".$properties['level-name']."/players/".$_GET['player']."_lock.dat") )
				die();

			$datafile = MINECRAFT_PATH."/".$properties['level-name']."/players/".$_GET['player']."_lock.dat";
			$level = new NBT();
			$level->loadFile($datafile);
			foreach($level->root[0]['value'] as $key1=>$tag)
			{
				if($tag['name'] == "Inventory")
				{
					foreach($tag['value']['value'] as $key2=>$conv)
					{
						$item = array();
						foreach($conv as $conveach) $item[$conveach['name']]= $conveach['value'];
						if($item['Slot'] == $_GET['slot']) unset($level->root[0]['value'][$key1]['value']['value'][$key2]);
					}
				}
			}
			$level->writeFile($datafile);
			die();
		}
	}



?>