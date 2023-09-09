<div id="menu">
<?php
	//functions
	include_once("lib/functions.php");
	//mysql init
	include_once("lib/connect.php");
	//data
	include_once("data/validation.php");
	include_once("data/settings.php");
	
	$games = dbGetAllGames();
	foreach ($games as $game) {
		$uStr = $_SETTINGGLOBAL['url'].str_replace('%GAME%',$game['nurl'],$_URI['game']);
		echo '<div class="ranking-game">';
		echo '<h3><a href="'.$uStr.'"><img src="'.$game['imgfull'].'" title="'.$game['nfull'].'" />'.$game['nshort'].'</a></h3>';
		echo '</div>';
	}
?>
</div>