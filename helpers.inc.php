<?php

	/*
	Mincraft Monitor Webservice object: Version 0.2
	http://www.twosphere.com.au
	http://www.minecraftmonitor.com.au

	All code is provided by Twosphere for testing purposes only.
	This code has not been thoroughly tested under all conditions. Twosphere, therefore, cannot guarantee or imply reliability, serviceability, or function of these programs.
	All programs contained herein are provided to you "AS IS" without any warranties of any kind.
	The implied warranties of non-infringement, merchantability and fitness for a particular purpose are expressly disclaimed.

	date: 2011-07-23
	File: helpers.inc.php
	Contains the functions needed to integrate with the minecraft server program.
	*/



function human_filesize($size)
{
	$mod = 1024;
	$units = explode(' ','B KB MB GB TB PB');
	for ($i = 0; $size > $mod; $i++) {
		$size /= $mod;
	}
	return round($size, 2) . ' ' . $units[$i];
}

function getdouble($data)
{
	$out = unpack("d",strrev(substr($data,0,8)));
	return $out[1];
}
function getfloat($data)
{
	$out = unpack("f",strrev(substr($data,0,4)));
	return $out[1];
}
function getint($data)
{
	$out = unpack("i",strrev(substr($data,0,4)));
	return $out[1];
}
function getlong($data)
{
	$out = unpack("L",strrev(substr($data,0,4)));
	return $out[1];
}
function getshort($data)
{
	$out = unpack("S",strrev(substr($data,0,4)));
	return $out[1];
}

function minecraft_pos($contents)
{
	$handle = "Pos";
	$conpos = strpos($contents,$handle);
	$contents = substr($contents,$conpos,32+strlen($handle));
	$label = substr($contents,0,strlen($handle));
	$data = substr($contents,strlen($handle),32);

	$out['x'] = getdouble(substr($data,5,8));
	$out['y'] = getdouble(substr($data,13,8));
	$out['z'] = getdouble(substr($data,21,8));

	return $out;
}

function minecraft_int($handle,$contents)
{
	$conpos = strpos($contents,$handle);
	if($conpos !== false)
	{
		$sub = substr($contents,$conpos,8+strlen($handle));
		$data = substr($sub,strlen($handle),8);
		$out = getint(substr($data,0,4));
		return $out;
	}
}

function minecraft_spawn($contents)
{
	$out['x'] = minecraft_int("SpawnX",$contents);
	$out['y'] = minecraft_int("SpawnY",$contents);
	$out['z'] = minecraft_int("SpawnZ",$contents);
	return $out;
}

function minecraft_time($contents)
{
	$handle = chr(04)."Time";
	$conpos = strpos($contents,$handle);
	if($conpos !== false)
	{
		$sub = substr($contents,$conpos,11+strlen($handle));
		$data = substr($sub,strlen($handle),11);

		$out = getlong(substr($data,4,4));

		while($out>24000) $out -= 24000;

		return $out;
	}
}

function minecraft_raintime($contents)
{
	$handle = "rainTime";
	$conpos = strpos($contents,$handle);
	if($conpos !== false)
	{
		$sub = substr($contents,$conpos,11+strlen($handle));
		$data = substr($sub,strlen($handle),11);

		$out = getlong(substr($data,0,4));
		return $out;
	}
}

class minecraft
{
	private $pipein = "/home/user/mmpipein";
	private $pipeout = "/home/user/mmpipeout";

	public function send($command)
	{
		//Make sure the pipe is open or it will block php
		if($this->running())
			exec("echo \"".$command."\" > ".$this->pipein);
	}

	public function running()
	{
		exec("/bin/ps ax|grep ".MINECRAFT_BIN,$out);

		foreach($out as $line)
			if(strpos($line,"grep") === false)
				$proc_line[] = $line;

		if(isset($proc_line))	return true;
								return false;
	}

	public function stop()
	{
		while(true)
		{
			if(!$this->running()) break;
			$this->send("stop");
			sleep(3);
		}
	}

	public function backupworld()
	{
		//Prepare
		$properties = $this->properties();
		@mkdir(MINECRAFT_PATH."/backup/");


		if($this->running())
		{
			$this->send("say Backing up world...");
			$this->send("save-all");
			sleep(3);
			$this->send("save-off");
			sleep(2);
			exec("/bin/tar czf ".MINECRAFT_PATH."/backup/".strtotime("now")."-".$properties['level-name'].".tar.gz ".MINECRAFT_PATH."/".$properties['level-name']."/");
		}
		else
			exec("/bin/tar czf ".MINECRAFT_PATH."/backup/".strtotime("now")."-".$properties['level-name'].".tar.gz ".MINECRAFT_PATH."/".$properties['level-name']."/");

		if($this->running())
		{
			$this->send("save-on");
			$this->send("say Complete.");
		}
	}

	public function run()
	{
		//Create stdin connector
		if(file_exists($this->pipein)) unlink($this->pipein);
		if(file_exists($this->pipeout)) unlink($this->pipeout);
		passthru('/bin/mkfifo '.$this->pipein.' -m 666');
		touch($this->pipeout);

		if(!file_exists($this->pipein) || !file_exists($this->pipeout))
		{
			echo "Pipes were not created.";
			return false;
		}

		while(true)
		{
			if($this->running()) break;
			chdir(MINECRAFT_PATH);

			pclose(popen(JAVA_PATH.'java -Xmx'.MAX_MEMORY.'M -Xms'.MIN_MEMORY.'M -jar '.MINECRAFT_BIN.' nogui >>'.$this->pipeout.' 0<>'.$this->pipein.' 2>&1 &', 'r'));
			sleep(3);
		}
		return true;
	}

	public function bounce()
	{
		$this->send("say Server restart");
		sleep(4);
		while(!$this->running())
		{
			$this->stop();
			sleep(5);
		}
		$this->run();
	}

	public function updatebin()
	{
		$this->send("say Updating minecraft server...");
		sleep(2);
		$this->getbin();
		$this->send("say Done.");
		$this->bounce();
	}

	public function getbin()
	{
		exec("wget http://www.minecraft.net/download/minecraft_server.jar -O ".MINECRAFT_PATH."/".MINECRAFT_BIN);
	}

	public function console($command = "",$array = false)
	{
		if($command != "")
		{
			//Send the command
			$presize = filesize($this->pipeout);
			$this->send($command);

			//Wait for changes to be made
			$timeout = 0;
			$postsize = strlen(file_get_contents($this->pipeout));
			while($postsize == $presize && $timeout <= 4)
			{
				$timeout++;
				usleep(500000);
				$postsize = strlen(file_get_contents($this->pipeout));
			}

			//Fetch the changes
			$fp = fopen($this->pipeout, 'r');
			fseek($fp,$presize);
			$newlines = fgets($fp,$postsize - $presize);
			fclose($fp);

			return $newlines;
		}
		else
		{
			$log = array_reverse(explode("\n",file_get_contents($this->pipeout)));
			$linenumber = 0;
			foreach($log as $line)
			{
				$line = str_replace(chr(27)."[0m","",$line);
				$line = str_replace(chr(31),"",$line);
				$line = substr($line,1);

				if(trim($line) == "")
				{
					//Don't pass blank lines
				}
				/*else if(preg_match("/^>(.*)/",$line,$matches))
				{
					//Don't echo commands (bukkit)
				}
				*/
				else if(strpos($line,"Attempted to place a tile entity where there was no entity tile!") !== false)
				{
					//Wierd minecraft bug clogging up the log
				}
				else
				{
					$linenumber++;
					if($linenumber > LOG_LINES) break;
					$lineout[]=$line;
				}
			}
			$lineout = array_reverse($lineout);

			if(is_array($lineout))
			{
				if($array)
					return $lineout;
				else
					return implode("\n",$lineout);
			}
			else
				return false;
		}

	}

	public function properties($properties = "")
	{
		if($properties == "")
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
			return $properties;
		}
		else
		{
			var_dump($properties);

			return;

			$fhandle = fopen(MINECRAFT_PATH."/server.properties","w");
			fwrite($fhandle, "#Minecraft server properties\n");
			fwrite($fhandle, "#Generated by www.minecraftmonitor.com\n");
			fwrite($fhandle, "#".date("Y-m-d H:i:s")."\n");
			foreach($properties as $key=>$value)
			{
				fwrite($fhandle, $key."=".$value."\n");
			}
			fclose($fhandle);
		}
	}
}

class player
{
	private $handle = "";

	public function delete()
	{
		global $minecraft;
		if($handle == "") return false;
		$properties = $minecraft->properties();
		unlink(MINECRAFT_PATH."/".$properties['level-name']."/players/".ereg_replace("[^A-Za-z0-9\_]", "", $handle ).".dat");
	}
}


function recurse_rename($src,$dst){

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


?>
