<div id="header"><div id="header-container">
<?php
	//functions
	include_once("lib/functions.php");
	include_once("lib/pbkdf2.php");
	//mysql init
	include_once("lib/connect.php");
	//data
	include_once("data/validation.php");
	include_once("data/settings.php");
	
	dbClearExpiredSessions();
	
	
	
	echo '<ul id="head-main">';
	$uStr = getUrl();
	echo '<li class="focus"><a href="'.$uStr.'">Home</a></li>';
	$games = dbGetAllGames();
	foreach ($games as $game) {
		$uStr = getUrl('game','%GAME%',$game['nurl']);
		echo '<li><a href="'.$uStr.'">'.$game['nfull'].'</a></li>';
	}
	$uStr = getUrl('player-list');
	echo '<li><a href="'.$uStr.'">Players</a></li>';
	echo '</ul>';
	
	
	
	$account = dbLoggedIn();
	echo '<ul id="head-user">';
	if ($account) {
		echo '<li class="focus"><a href="'.getUrl('player','%PLAYER%',$account['nuser']).'">'.$account['ndisplay'].'</a></li>';
		echo '<li><a href="'.getUrl('player-edit','%PLAYER%',$account['nuser']).'">Settings</a></li>';
		echo $account['admin']?'<li><a href="'.getUrl('admin').'">Admin</a></li>':null;
		echo '<li><a href="'.getUrl('logout').'">Logout</a></li>';
	} else {
		echo '<li class="focus"><a href="'.getUrl('login').'">Login</a></li>';
		echo '<li><a href="'.getUrl('signup').'">Signup</a></li>';
	}
	echo '</ul>';
?>
</div></div>