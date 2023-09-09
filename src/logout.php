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
	
	
	
	//check if logged in
	$loggedin = isset($_COOKIE['login'])?dbLoggedIn($_COOKIE['login']):false;
	$message = 'You were never logged in.';
	$success = false;
	//process input data if available and not logged in
	if ($loggedin) {
		$ip = $_SERVER['REMOTE_ADDR'].(isset($_SERVER['HTTP_X_FORWARDED_FOR'])?'|'.$_SERVER['HTTP_X_FORWARDED_FOR']:'');
		$success = mysql_query('DELETE FROM sessions WHERE token="'.$_COOKIE['login'].'"') or die(mysql_error());
		if ($success) {
			$message = 'Logged out of '.$loggedin['ndisplay'].'.';
			setcookie('login','',time()-86400);
		}
	}
	//if after all that still not logged in, kill cookies
	if ($success) {
		$message = 'Logged out of '.$loggedin['ndisplay'].'.';
	}
?>	
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<?php echo '<meta http-equiv="refresh" content="'.$_SETTINGGLOBAL['redirect-delay'].'; url='.$_SETTINGGLOBAL['url'].$_URI['home'].'" />'; ?>
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
	echo '<div id="title"><h1>Logout</h1></div>';
	echo '<div id="content">';
	echo '<p>'.$message.'</p>';
	echo '<p>Redirecting to homepage in '.$_SETTINGGLOBAL['redirect-delay'].' seconds.</p>';
	echo '</div>';
	echo '</div>';
	
	include("embed-footer.php");
?>
		</div>
	</body>
</html>