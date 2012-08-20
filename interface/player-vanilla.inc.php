<?php
if(isset($_GET['pn']['op']))
{
	$menu['deop']['command'] = "deop ".$name;
}
else
{
	$menu['op']['command'] = "op ".$name;
}

if(isset($_GET['pn']['banned']))
{
	$menu['pardon']['command'] = "pardon ".$name;
}
else
{
	$menu['ban']['command'] = "ban ".$name;
}

if(isset($_GET['pn']['connected']))
{
	$menu['kick']['command'] = "kick ".$name;
}

$menu['delete']['function'] = "player_delete";
$menu['delete']['command'] = $name;
?>
