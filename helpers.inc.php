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


//Security, ignore this line
//if (!empty($_SERVER['SCRIPT_FILENAME']) && 'comm.php' != basename($_SERVER['SCRIPT_FILENAME']))die ('Loading this page directly will bring dishonor to your family.');

	
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


function minecraft_send($command)
{
	//Make sure the pipe is open or it will block php
	if(minecraft_running())
		exec("echo \"".$command."\" > /tmp/minein");
}


function minecraft_running()
{
	exec("/bin/ps ax|grep ".MINECRAFT_BIN,$out);

	foreach($out as $line)
		if(strpos($line,"grep") === false)
			$proc_line[] = $line;

	if(isset($proc_line))	return true;
							return false;
}

function minecraft_stop()
{
	while(true)
	{
		if(!minecraft_running()) break;
		minecraft_send("stop");
		sleep(3);
	}
}

function minecraft_backupworld()
{
	if(minecraft_running())
	{
		minecraft_send("say Backing up world...");
		minecraft_send("save-all");
		sleep(3);
		minecraft_send("save-off");
		sleep(2);
	
		//unlink( MINECRAFT_PATH."/backup/current-world.tar.gz" );
		exec("/bin/tar czf ".MINECRAFT_PATH."/backup/".strtotime("now")."-world.tar.gz ".MINECRAFT_PATH."/world/");
	}
	else
		exec("/bin/tar czf ".MINECRAFT_PATH."/backup/".strtotime("now")."-world.tar.gz ".MINECRAFT_PATH."/world/");

	if(minecraft_running())
	{
		minecraft_send("save-on");
		minecraft_send("say Complete.");
	}
}

function minecraft_run()
{
	//Create stdin connector
	if(file_exists('/tmp/minein')) unlink('/tmp/minein');
	passthru('mkfifo /tmp/minein -m 666');

	while(true)
	{
		if(minecraft_running()) break;
		chdir(MINECRAFT_PATH);
		pclose(popen(JAVA_PATH.'bin/java -Xmx4090M -Xms1024M -jar '.MINECRAFT_BIN.' nogui >>server.mon 0<>/tmp/minein 2>&1 &', 'r'));
		sleep(3);
	}
}

function minecraft_bounce()
{
	minecraft_send("say Server restart");
	sleep(2);
	minecraft_stop();
	sleep(1);
	minecraft_backupworld();
	sleep(1);
	minecraft_run();
}

function minecraft_updatebin()
{
	minecraft_send("say Updating minecraft server...");
	sleep(2);
	minecraft_getbin();
	minecraft_send("say Done.");
	minecraft_bounce();

}

function minecraft_getbin()
{
	exec("wget http://www.minecraft.net/download/minecraft_server.jar -O ".MINECRAFT_PATH."/".MINECRAFT_BIN);
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