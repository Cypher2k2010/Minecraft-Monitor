<?php

	/*
	Mincraft Monitor Webservice object: Version 0.36
	http://www.twosphere.com.au
	http://www.minecraftmonitor.com.au
	
	All code is provided by Twosphere for testing purposes only.
	This code has not been thoroughly tested under all conditions. Twosphere, therefore, cannot guarantee or imply reliability, serviceability, or function of these programs.
	All programs contained herein are provided to you "AS IS" without any warranties of any kind.
	The implied warranties of non-infringement, merchantability and fitness for a particular purpose are expressly disclaimed.

	date: 2011-07-23	
	File: access.inc.php
	Handles authentication with minecraftmonitor.com
	*/
	
	
	//Security, ignore this line
	if (!empty($_SERVER['SCRIPT_FILENAME']) && 'comm.php' != basename($_SERVER['SCRIPT_FILENAME']))die ('Loading this page directly will bring dishonor to your family.');

	if(SERVER_PASS != "")
	{
		$APIKEY = substr(base64_encode(sha1($_SERVER['REMOTE_ADDR'].@date("Y-m-d").md5(strrev(md5(@date("Y-m-d").SERVER_PASS))))),0,-2);
		if(isset($_GET['challenge']))
		{
			$challengestamp = md5(number_format(@date("U")/100,0));	
			$challenge_me = md5("yommay".SERVER_PASS."salt".$challengestamp."isyommay".SELF_VERSION);	
			$challenge_you = md5($_SERVER['REMOTE_ADDR'].SERVER_PASS.$challenge_me.$_SERVER['REMOTE_ADDR'].SELF_VERSION);
			if(isset($_GET['challenge']) && $_GET['challenge'] == "yes") die($challenge_me);
			if(isset($_GET['challenge']))
			{
				if($_GET['challenge'] != $challenge_you)
					die(json_encode(array("error"=>"access denied","reason"=>"failed challenge")));
				else
					die($APIKEY); //Send API KEY.
			}
			else
				die(json_encode(array("error"=>"access denied","reason"=>"incorrect usage")));
		}
		
		//Check for permission
		if(isset($_GET['key']) && $_GET['key'] == $APIKEY)
		{
			$access_api = true;
		}
		else if(isset($_GET['key']))
		{
			die(json_encode(array("error"=>"access denied","reason"=>"expired key")));
		}
		else if(!isset($_GET['key']))
		{
			$json['ident'] = "Mincecraft Monitor";
			$json['url'] = "http://www.minecraftmonitor.com";
			$json['version'] = SELF_VERSION;		
			die(json_encode($json));
		}
	}

?>
