<?php
	$cli_colour['black'] = '30;0';
	$cli_colour['dark_gray'] = '30;1';
	$cli_colour['blue'] = '34;0';
	$cli_colour['light_blue'] = '34;1';
	$cli_colour['green'] = '32;0';
	$cli_colour['light_green'] = '32;1';
	$cli_colour['cyan'] = '36;0';
	$cli_colour['light_cyan'] = '36;1';
	$cli_colour['red'] = '31;0';
	$cli_colour['light_red'] = '31;1';
	$cli_colour['purple'] = '35;0';
	$cli_colour['light_purple'] = '35;1';
	$cli_colour['brown'] = '33;0';
	$cli_colour['yellow'] = '33;1';
	$cli_colour['light_gray'] = '37;0';
	$cli_colour['white'] = '37;1';
	$cli_colour_code = array_flip($cli_colour);
	$cli_colour_code['30;22'] = 'black';
	$cli_colour_code['33;22'] = 'yellow';
	$cli_colour_code['35;22'] = 'light_purple';


	function no_colour($line)
	{

		$colour_regex = "/"."\033"."\[(\d*;\d*)\m/";
		$line = preg_replace($colour_regex,"",$line);
		$colour_regex = "/"."\033"."\[\m/";
		$line = preg_replace($colour_regex,"",$line);
		return $line;

	}


	function cli_colour($line)
	{
		global $cli_colour_code;

		$opentags = 0;
		//Find colours
		$colour_regex = "/"."\033"."\[(\d*;\d*)\m/";
		$matchcount = preg_match($colour_regex,$line,$match);

		while($matchcount == 1)
		{
			if(isset($cli_colour_code[$match[1]]))
			{
				if($opentags > 0)
				{
					$opentags--;
					$prespan = "</span>";
				}
				else
					$prespan = "";

				$pos = strpos($line,$match[0]);
				$piece[0] = substr($line,0,$pos);
				$piece[1] = $prespan."<span class='".$cli_colour_code[$match[1]]."'>";
				$piece[2] = substr($line,$pos+strlen($match[0]));

				$opentags++;
				$line = implode("",$piece);
			}
			else
			{
				$pos = strpos($line,$match[0]);
				$piece[0] = substr($line,0,$pos);
				$piece[1] = $match[1];
				$piece[2] = substr($line,$pos+strlen($match[0]));
				$line = implode("",$piece);
			}

			$matchcount = preg_match($colour_regex,$line,$match);
		}

		//Find colours
		$colour_regex = "/"."\033"."\[\m/";
		$matchcount = preg_match($colour_regex,$line,$match);
		while($matchcount == 1)
		{
			if($opentags > 0)
			{
				$pos = strpos($line,$match[0]);
				$piece[0] = substr($line,0,$pos);
				$piece[1] = "</span>";
				$piece[2] = substr($line,$pos+strlen($match[0]));
				$opentags--;
				$line = implode("",$piece);
			}
			else
			{
				$pos = strpos($line,$match[0]);
				$piece[0] = substr($line,0,$pos);
				$piece[1] = "";
				$piece[2] = substr($line,$pos+strlen($match[0]));
				$line = implode("",$piece);
			}

			$matchcount = preg_match($colour_regex,$line,$match);
		}

		return $line;
	}

	function scrubline($line)
	{
		$line = preg_replace("/>\015/","",$line);
		$line = preg_replace("/^\n/","",$line);
		//$line = preg_replace("/^>(.*)/","",$line);
		$line = preg_replace("/>$/","",$line);

		return $line;
	}

	function translateline($line)
	{
		foreach(glob("./consolefilter/filter-*.inc.php") as $includefile)
		{
			require($includefile);
		}

		return $line;
	}



?>
