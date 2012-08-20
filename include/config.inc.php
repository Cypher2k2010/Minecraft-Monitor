<?php

	/*
	Mincraft Monitor Webservice object: Version 0.2
	http://www.twosphere.com.au
	http://www.minecraftmonitor.com.au

	All code is provided by Twosphere for testing purposes only.
	This code has not been thoroughly tested under all conditions. Twosphere, therefore, cannot guarantee or imply reliability, serviceability, or function of these programs.
	All programs contained herein are provided to you "AS IS" without any warranties of any kind.
	The implied warranties of non-infringement, merchantability and fitness for a particular purpose are expressly disclaimed.

	date: 2012-08-20
	File: config.inc.php
	Global configuration for webservices.
	*/

	class config
	{
		private static $data = array();

		public static function set($key,$value)
		{
			self::read();
			self::$data[$key] = $value;

			return self::$data[$key];
		}

		public static function get($key)
		{
			self::read();
			if(isset(self::$data[$key]))
				return self::$data[$key];
			else
				return false;
		}

		public static function read()
		{
			if(count(self::$data) == 0)
			{
				self::$data = json_decode(file_get_contents("config.json"),true);
			}
			return self::$data;
		}

		public static function write()
		{
			file_put_contents("config.json",json_encode(self::$data));
			return self::$data;
		}
	}

?>
