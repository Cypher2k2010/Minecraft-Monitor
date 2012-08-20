<?php
	date_default_timezone_set("UTC");
	session_start();

	include("include/config.inc.php");
	include("consolefilter/filter.inc.php");
	include("include/objects.inc.php");
	include("include/data.inc.php");


	if(isset($_GET['action']))
	{
		switch($_GET['action'])
		{

			case "verify":

				if(isset($_GET['path']))
				{
					if(!is_dir($_GET['path']))
					{
						die("Folder does not exist.");
					}
					if(!is_writable($_GET['path']) && isset($_GET['write']))
					{
						die("Folder is read only!");
					}
					echo "ok";
				}

				if(isset($_GET['file']))
				{
					if(!file_exists($_GET['file']))
					{
						die("The file does not exist.");
					}

					if(!is_readable($_GET['file']))
					{
						die("The file cannot be read.");
					}

					if(!is_writable($_GET['file']) && isset($_GET['write']))
					{
						die("The file is read only.");
					}
					echo "ok";
				}

				if(isset($_GET['java']))
				{
					if(!is_dir($_GET['java']) && $_GET['java'] != "")
					{
						die("Folder does not exist.");
					}

					if(!file_exists($_GET['java']."java") && $_GET['java'] != "")
					{
						die("Java was not found.");
					}

					passthru($_GET['java']."java -version");
				}



			break;

		}



	}


?>
