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

class minecraft
{
	private $pipein = "";
	private $pipeout = "";

	public function __construct()
	{
		$this->pipein = config::get('PIPE_PATH')."/mmpipein";
		$this->pipeout = config::get('PIPE_PATH')."/mmpipeout";
	}

	public function send($command)
	{
		//Make sure the pipe is open or it will block php
		if($this->running())
			exec("echo \"".$command."\" > ".$this->pipein);
	}

	public function running()
	{
		exec("/bin/ps ax|grep ".config::get('MINECRAFT_BIN'),$out);

		foreach($out as $line)
			if(strpos($line,"grep") === false)
				$proc_line[] = $line;

		if(isset($proc_line))	return true;
								return false;
	}


	public function kill()
	{
		$psout = exec("ps aux | grep ".substr(config::get('MINECRAFT_BIN'),0,strlen(config::get('MINECRAFT_BIN'))-1)."[".substr(config::get('MINECRAFT_BIN'),-1)."]");
		if($psout)
		{
			$psout_array = array();
			preg_match("/\w+\s+(\d+)\s+(\d+\.\d+)\s+(\d+\.\d+)/",$psout,$psout_array);

			if(isset($psout_array[1]))
			{
				exec("kill ".$psout_array[1]);
				return true;
			}
			else
			{
				return false;
			}
		}
	}

	public function stop()
	{
		$loopcount = 0;
		while(true)
		{
			$loopcount++;
			if(!$this->running()) break;

			if($loopcount < 5)
				$this->send("stop");
			else
				if(!$this->kill()) break;

			sleep(2);
		}
	}

	public function backupworld()
	{
		//Prepare
		$properties = $this->properties();
		@mkdir(config::get('MINECRAFT_PATH')."/backup/");


		if($this->running())
		{
			$this->send("say Backing up world...");
			$this->send("save-all");
			sleep(3);
			$this->send("save-off");
			sleep(2);
			exec("/bin/tar czf ".config::get('MINECRAFT_PATH')."/backup/".strtotime("now")."-".$properties['level-name'].".tar.gz ".config::get('MINECRAFT_PATH')."/".$properties['level-name']."/");
		}
		else
			exec("/bin/tar czf ".config::get('MINECRAFT_PATH')."/backup/".strtotime("now")."-".$properties['level-name'].".tar.gz ".config::get('MINECRAFT_PATH')."/".$properties['level-name']."/");

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
		passthru('mkfifo '.$this->pipein.' -m 666');
		touch($this->pipeout);

		if(!file_exists($this->pipein) || !file_exists($this->pipeout))
		{
			echo "Pipes were not created.\n";
			echo $this->pipein." - ".file_exists($this->pipein)."\n";
			echo $this->pipeout." - ".file_exists($this->pipeout)."\n";
			return false;
		}

		while(true)
		{
			if($this->running()) break;
			chdir(MINECRAFT_PATH);

			pclose(popen(config::get('JAVA_PATH').'java -Xmx'.config::get('MAX_MEMORY').'M -Xms'.config::get('MIN_MEMORY').'M -jar '.config::get('MINECRAFT_BIN').' nogui >>'.$this->pipeout.' 0<>'.$this->pipein.' 2>&1 &', 'r'));
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
		exec("wget http://www.minecraft.net/download/minecraft_server.jar -O ".config::get('MINECRAFT_PATH')."/".config::get('MINECRAFT_BIN'));
	}

	public function console($command = "", $array = false, $suppress=false)
	{
		if($command != "")
		{
			//Send the command
			$presize = filesize($this->pipeout);
			$this->send($command);

			//Wait for changes to be made
			$timeout = 0;
			$poststring = "";
			while($poststring == "" && $timeout <= 4)
			{
				$timeout++;
				usleep(500000);
				$poststring = file_get_contents($this->pipeout,false,null,$presize);
			}

			if($suppress)
				file_put_contents($this->pipeout,file_get_contents($this->pipeout,false,null,-1,$presize));

			return $poststring;
		}
		else
		{
			$logfile = file_get_contents($this->pipeout);

			if($logfile !="")
			{
				if(strpos(">\015",$logfile) !== false)
					$log = array_reverse(explode("\n>\015",$logfile));
				else
					$log = array_reverse(explode("\n",$logfile));
			}
			else
				return;

			$linenumber = 0;
			$lineout = array();
			foreach($log as $line)
			{
				$line = str_replace(chr(31),"",$line);
				$line = substr($line,1);
				$line = translateline($line);

				if(trim($line) == "")
				{
					//Don't pass blank lines
				}
				else
				{
					$linenumber++;
					if($linenumber > config::get('LOG_LINES')) break;
					$lineout[]=$line;
				}
			}

			if(is_array($lineout))
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
			if(file_exists(config::get('MINECRAFT_PATH')."/server.properties"))
			{
				$settings = file_get_contents(config::get('MINECRAFT_PATH')."/server.properties");
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
			return;

			$fhandle = fopen(config::get('MINECRAFT_PATH')."/server.properties","w");
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

	public function __construct($handle)
	{
		global $minecraft;

		$this->handle = $handle;

	}

	public function delete()
	{
		global $minecraft;
		if($this->handle == "") return false;
		$properties = $minecraft->properties();
		unlink(config::get('MINECRAFT_PATH')."/".$properties['level-name']."/players/".ereg_replace("[^A-Za-z0-9\_]", "", $this->handle ).".dat");
	}
}

?>
