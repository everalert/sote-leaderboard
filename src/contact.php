<?php
	//functions
	include_once("lib/functions.php");
	//mysql init
	include_once("lib/connect.php");
	//data
	include_once("data/validation.php");
	include_once("data/settings.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<style type="text/css">@import url("<?php echo $_SETTINGGLOBAL['url']; ?>/style.css");</style>
		<link rel="icon" type="image/png" href="http://puu.sh/8mEj2.png" />
		<script type="text/javascript" src="<?php echo $_SETTINGGLOBAL['url']; ?>/jquery.js"></script> <!-- jquery 2.1.1 -->
		<script type="text/javascript" src="<?php echo $_SETTINGGLOBAL['url']; ?>/behaviour.js"></script>
		<title>SOTE Rankings</title>
	</head>
	<body>
		<div id="container">
<?php
	include("embed-header.php");
	include("embed-menuhome.php");
	
	
	
	
	
	echo '<div id="page">';
	echo '<div id="title"><h1>Contact</h1></div>';
	echo '<div id="content"><div id="contact">';
	echo '<p>If you have any issues or questions about the rankings, please contact one of these people.</p>';
	echo '<h2>Admins</h2>';
	$admins = dbGetAllAdmins();
	foreach ($admins as $admin) {
		echo '<div class="contact-player">';
		echo '<h3>'.$admin['ndisplay'].'</h3>';
		echo '<p><a href="'.getUrl('player','%PLAYER%',$admin['nuser']).'"><img src="http://puu.sh/8llrs.png" />Profile</a></p>';
		echo strlen($admin['ttvid'])>0?'<p><a href="http://twitch.tv/'.$admin['ttvid'].'"><img src="http://puu.sh/8hCAI.png" />Twitch</a></p>':'';
		echo strlen($admin['ytid'])>0?'<p><a href="http://youtube.com/'.$admin['ytid'].'"><img src="http://puu.sh/8hCz8.png" />YouTube</a></p>':'';
		echo strlen($admin['twid'])>0?'<p><a href="http://twitter.com/'.$admin['twid'].'"><img src="http://puu.sh/8hCXC.png" />Twitter</a></p>':'';
		echo '</div>';
	}
	$games = dbGetAllGames();
	foreach ($games as $game) {
		echo '<h2>'.$game['nfull'].'</h2>';
		$modcount = 0;
		foreach (explode(' ',$game['moderators']) as $modid) {
			if ($user = dbGetUserById($modid)) {
				echo '<div class="contact-player">';
				echo '<h3>'.$user['ndisplay'].'</h3>';
				echo '<p><a href="'.getUrl('player','%PLAYER%',$user['nuser']).'"><img src="http://puu.sh/8llrs.png" />Profile</a></p>';
				echo strlen($user['ttvid'])>0?'<p><a href="http://twitch.tv/'.$user['ttvid'].'"><img src="http://puu.sh/8hCAI.png" />Twitch</a></p>':'';
				echo strlen($user['ytid'])>0?'<p><a href="http://youtube.com/'.$user['ytid'].'"><img src="http://puu.sh/8hCz8.png" />YouTube</a></p>':'';
				echo strlen($user['twid'])>0?'<p><a href="http://twitter.com/'.$user['twid'].'"><img src="http://puu.sh/8hCXC.png" />Twitter</a></p>':'';
				echo '</div>';
				$modcount+=1;
			}
		}
		if (!$modcount) {
			echo '<p>This game has no moderators.</p>';
		}
	}
	echo '</div></div>';
	echo '</div>';
	
	include("embed-footer.php");
?>
		</div>
	</body>
</html>