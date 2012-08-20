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
	File: data.inc.php
	Data translation and conection.
	*/	
	
	
	function dev2array($dev)
	{
		$ifs = explode("\n",$dev);
		foreach($ifs as $if)
		{
			if(strpos($if,":") !== false)
			{
				$if = str_replace(":"," ",$if);

				$sarray = explode(" ",$if);
					foreach($sarray as $itm)
					{
							if($itm != "") $oarray[] = $itm;
					}

					list($if,$rxbytes,$rxpackets,$rxerrs,$rxdrop,$rxfifo,$rxframe,$rxcompressed,$rxmulticast
					,$txbytes,$txpackets,$txerrs,$txdrop,$txfifo,$txcolls,$txcarrier,$txcompressed) = $oarray;
				unset($oarray);
				unset($sarray);

				$outray[$if] = compact("if","rxbytes","rxpackets","rxerrs","rxdrop","rxfifo","rxframe","rxcompressed","rxmulticast","txbytes","txpackets","txerrs","txdrop","txfifo","txcolls","txcarrier","txcompressed");
			}
		}

		if(isset($outray)) return $outray;
	}


	function cpuinfo2array($cpuinfo)
	{
		$cpugroup = explode("\n\n",$cpuinfo);
		foreach($cpugroup as $cpuitem)
		{
			$cpudata = explode("\n",$cpuitem);
			unset($cpudata_array);
			foreach($cpudata as $cpudataitem)
			{
				if(trim($cpudataitem) != "")
				{
					@list($cpu_detail,$cpu_value) = explode(":",$cpudataitem);
					$cpudata_array[trim($cpu_detail)] = trim($cpu_value);
				}
			}

			if(isset($cpudata_array)) $outray[]=$cpudata_array;
		}
		if(isset($outray)) return $outray;
	}
?>