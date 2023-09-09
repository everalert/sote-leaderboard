<?php
	//functions
	include_once("lib/functions.php");
	//mysql init
	include_once("lib/connect.php");
	//data
	include_once("data/validation.php");
	include_once("data/settings.php");
	
	//check for player
	if (isset($_GET['p'])) {
		$player = dbGetUser($_GET['p']);
		if (!$player) { include("embed-menuhome.php"); }
	} elseif (isset($_GET['t'])) {
		$time = dbGetTime($_GET['t']);
		if (!$time) {
			include("embed-menuhome.php");
			$player = false;
		} else {
			$player = dbGetUserById($time['userid']);
			if (!$player) { include("embed-menuhome.php"); }
		}
	} else {
		include("embed-menuhome.php");
		$player = false;
	}
	//output
	if ($player) {
		echo '<div id="menu">';
		echo '<h2>'.$player['ndisplay'].'</h2>';
		echo '<h3><a href="'.getUrl('player','%PLAYER%',$player['nuser']).'">Profile</a></h3>';
		$totals = mysql_query('SELECT gameid FROM totals WHERE userid='.$player['id'].' ORDER BY gameid,id ASC') or die('<p>'.mysql_error().'</p>');
		while ($total = mysql_fetch_array($totals)) {
			$game = dbGetGameById($total['gameid']);
			echo '<h3><a href="'.getUrl('player-game',array('%GAME%','%PLAYER%'),array($game['nurl'],$player['nuser'])).'">'.$game['nfull'].'</a></h3>';
			echo '<ul>';
			echo '<li><a href="'.getUrl('player-game-history',array('%GAME%','%PLAYER%'),array($game['nurl'],$player['nuser'])).'">'.$game['nshort'].' Time History</a></li>';
			echo '</ul>';
		}
		echo '</div>';
	}
?>