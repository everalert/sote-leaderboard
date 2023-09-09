<div id="menu">
<?php
	//functions
	include_once("lib/functions.php");
	//mysql init
	include_once("lib/connect.php");
	//data
	include_once("data/validation.php");
	include_once("data/settings.php");
	
	
	if (isset($_GET['g'])) {
		$game = dbGetGame($_GET['g']);
	} else {
		$game = false;
		echo '<p>Why did you embed this here wtf?</p>';
	}
	if ($game) {
		echo '<p><a href="'.getUrl('game','%GAME%',$game['nurl']).'"><img src="'.$game['imgfull'].'" title="'.$game['nfull'].'" /></a></p>';
		//get ranking list
		echo '<div class="ranking-menu">';
		echo '<h3>Rankings</h3>';
		echo '<ul>';
		$rankings = dbGetAllRankings($game['nurl']);
		foreach ($rankings as $rank) {
			echo '<li><a href="'.getUrl('ranking',array('%GAME%','%RANKING%'),array($game['nurl'],$rank['nurl'])).'">'.$rank['nfull'].'</a></li>';
		}
		echo '</ul>';
		echo '</div>';
		echo '<div class="ranking-menu">';
		echo '<h3>Information</h3>';
		echo '<ul>';
		echo '<li><a href="'.getUrl('game','%GAME%',$game['nurl']).'">Records</a></li>';
		echo '<li><a href="'.getUrl('game-rules','%GAME%',$game['nurl']).'">Rules</a></li>';
		echo '<li><a href="'.getUrl('game-leaders','%GAME%',$game['nurl']).'">Leaders</a></li>';
		echo '<li><a href="'.getUrl('game-twitch','%GAME%',$game['nurl']).'">Live Streams</a></li>';
		//echo '<li><a href="'.getUrl('game-stats','%GAME%',$game['nurl']).'">Statistics</a></li>';
		//echo '<li><a href="'.getUrl('game-pointless','%GAME%',$game['nurl']).'">Pointless Times</a></li>';
		echo '</ul>';
		echo '</div>';
	}
?>
</div>