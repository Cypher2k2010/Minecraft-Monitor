<?php

function flip($i,$h=1,$v=0)
{
	$width = imagesx($i);
	$height = imagesy($i);
	$temp = imagecreatetruecolor($width,$height);
	imagecopy($temp,$i,0,0,0,0,$width,$height);

	if ($h==1)
	{
		for ($x=0 ; $x<$width ; $x++)
		{
			imagecopy($i, $temp, $width-$x-1, 0, $x, 0, 1, $height);
		}
		imagecopy($temp,$i,0,0,0,0,$width,$height);
	}

	if($v==1)
	{
		for ($x=0; $x<$height ; $x++)
		{
			imagecopy($i, $temp, 0, $height-$x-1, 0, $x, $width, 1);
		}
	}
	return $i;
}

header('Content-type: image/png');

$username= $_GET['name'];

if(isset($_GET['full']))
{
	$imagesrcheight = 32;
	$imageheight = 128;
	$filename = "skins/".$username."_full.png";
}
else
{
	$imagesrcheight = 16;
	$imageheight = 32;
	$imagewidth = 32;
	$filename = "skins/".$username.".png";
}

if(file_exists($filename) && time() - filemtime($filename) >= 86400)
{
	echo file_get_contents($filename);
}
else
{
	$pngraw = file_get_contents("http://s3.amazonaws.com/MinecraftSkins/".$username.".png");
	if(strlen($pngraw)==0)
		$pngraw = file_get_contents("files/playerskin/char.png");

	// Create image instances
	$src = imagecreatefromstring($pngraw);

	$working = imagecreatetruecolor(16, $imagesrcheight);
	imagealphablending($working,true);

	imagefill($working,0,0,imagecolorallocate($working,180,180,180));

	$dest = imagecreatetruecolor($imagewidth, $imageheight);
	imagealphablending($dest,true);

	$limb = imagecreatetruecolor(4, 14);
	imagealphablending($limb,true);

	//Head
	imagecopymerge ( $working , $src , 4 , 0 , 8 , 8 , 8 , 8 , 100);

	//Body
	imagecopymerge ( $working , $src , 4 , 8 , 20 , 20 , 8 , 12 , 100);

	//Legs
	imagecopymerge ( $limb , $src , 0 , 0 , 4 , 20 , 4 , 12 , 100);
	imagecopymerge ( $working , $limb , 4 , 20 , 0 , 0 , 4 , 12 , 100);
	imagecopymerge ( $working , flip($limb) , 8 , 20 , 0 , 0 , 4 , 12 , 100);

	//Arms
	imagecopymerge ( $limb , $src , 0 , 0 , 44 , 20 , 4 , 12 , 100);
	imagecopymerge ( $working , $limb , 0 , 8 , 0 , 0 , 4 , 12 , 100);
	imagecopymerge ( $working , flip($limb) , 12 , 8 , 0 , 0 , 4 , 12 , 100);

	imagecopyresized ( $dest , $working , 0 , 0 , 0 , 0 , $imagewidth , $imageheight , 16 , $imagesrcheight);

	// Output and free from memory
	imagepng($dest,$filename);
	imagepng($dest);

	imagedestroy($dest);
	imagedestroy($working);
	imagedestroy($src);
	imagedestroy($limb);
}
?>
