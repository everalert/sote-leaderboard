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
	$message = $loggedin?'Logged in as '.$loggedin['ndisplay'].'.':'';
	$success = false;
	//process input data if available and not logged in
	if (isset($_POST['validate']) && isset($_POST['user']) && isset($_POST['pass']) && !$loggedin) {
		$ip = $_SERVER['REMOTE_ADDR'].(isset($_SERVER['HTTP_X_FORWARDED_FOR'])?'|'.$_SERVER['HTTP_X_FORWARDED_FOR']:'');
		$uStr = $_SETTINGGLOBAL['url'].$_URI['login'];
		$message = 'Incorrect credentials. <a href="'.$uStr.'">Try again</a>.';
		if (preg_match('/'.$_VALIDATION['urlplayer'].'/',strtolower($_POST['user']))) {
			$lock = mysql_num_rows(mysql_query('SELECT * FROM sessions WHERE token="'.$ip.'" AND type="acctlock"'));
			//check if ip is locked
			if ($lock>0) {
				$success = false;
				$message = 'Login locked for a while due to too many failed login attempts.';
			} else {
				//get user info and check for validity
				$user = mysql_query('SELECT id,nuser,ndisplay,password FROM users WHERE nuser="'.strtolower($_POST['user']).'"');
				$user = mysql_fetch_array($user);
				if ($user) {
					//validate password and generate valid token
					$success = validate_password($_POST['pass'],$user['password']);
					$tokenexists = 1;
					while ($tokenexists>0) {
						$token = base64_encode(mcrypt_create_iv(24));
						$tokenexists = mysql_num_rows(mysql_query('SELECT token FROM sessions WHERE token="'.$token.'"'));
					}
					if ($success) {
						//update login session token and cookie on successful validation and delete passfail record
						$expires = isset($_POST['long'])?$_SETTINGGLOBAL['login-session-time-long']:$_SETTINGGLOBAL['login-session-time'];
						$query = mysql_query('REPLACE INTO sessions (userid,type,token,expires) VALUES ('.$user['id'].',"login","'.$token.'",'.$expires.')');
						if ($query) {
							$message = $query?'Successfully logged in as '.$user['ndisplay'].'.':'';
							$expires = isset($_POST['long'])?$_SETTINGGLOBAL['login-session-time-long']:$_SETTINGGLOBAL['login-session-time'];
							setcookie('login',$token,time()+$expires);
							mysql_query('DELETE FROM sessions WHERE token="'.$ip.'" AND type="passfail"');
						}
					} else {
						//on failed validation add to fail counter
						$data = mysql_query('SELECT value FROM sessions WHERE token="'.$ip.'" AND type="passfail"');
						$data = mysql_fetch_array($data);
						mysql_query('REPLACE INTO sessions (userid,type,token,expires,value) VALUES ('.$user['id'].',"passfail","'.$ip.'",'.$_SETTINGGLOBAL['login-lock-time'].','.($data['value']+1).')');
						$data = mysql_query('SELECT value FROM sessions WHERE token="'.$ip.'" AND type="passfail"');
						$data = mysql_fetch_array($data);
						if ($data['value']>=$_SETTINGGLOBAL['login-fail-limit']) {
							//if fail counter too high lock account for a while
							mysql_query('DELETE FROM sessions WHERE userid='.$user['id']);
							$query = mysql_query('INSERT INTO sessions (userid,type,token,expires) VALUES ('.$user['id'].',"acctlock","'.$ip.'",'.$_SETTINGGLOBAL['login-lock-time'].')');
							$message = $query?'Login failed too many times in a row. Login locked for '.$_SETTINGGLOBAL['login-lock-text'].'.':'';
						}
					}
				}
			}
		}
	}
	//if after all that still not logged in, kill cookies
	if (!$success && !$loggedin) {
		setcookie('login','',time()-86400);
	}
	
	//with help from https://crackstation.net/hashing-security.htm
?>	
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<?php echo $success||$loggedin?'<meta http-equiv="refresh" content="'.$_SETTINGGLOBAL['redirect-delay'].'; url='.$_SETTINGGLOBAL['url'].$_URI['home'].'" />':''; ?>
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
	echo '<div id="title"><h1>Login</h1></div>';
	echo '<div id="content">';
	if ($loggedin) {
		showSuccessMessage($message);
		echo '<p>Redirecting to homepage in '.$_SETTINGGLOBAL['redirect-delay'].' seconds.</p>';
	} elseif (isset($_POST['validate']) && isset($_POST['user']) && isset($_POST['pass'])) {
		if ($success) {
			showSuccessMessage($message);
			echo '<p>Redirecting to homepage in '.$_SETTINGGLOBAL['redirect-delay'].' seconds.</p>';
		} else {
			showErrorMessage($message);
		}
	} else {
		$uStr = $_SETTINGGLOBAL['url'].$_URI['login'];
		echo '<form name="login" action="'.$uStr.'" method="post">';
		echo '<input type="hidden" name="validate" value="1" />';
		echo '<label><span class="name">Username</span><span class="field"><input type="text" name="user" /></span></label>';
		echo '<label><span class="name">Password</span><span class="field"><input type="password" name="pass" /></span></label>';
		echo '<label><span class="field"><input type="checkbox" name="long" value="1" /><span class="check"></span><span class="checktext">Stay logged in for '.$_SETTINGGLOBAL['login-session-time-long-text'].'</span></span></label>';
		echo '<div class="label"><span class="field"><input type="submit" value="Login" /></span></div>';
		echo '</form>';
	}
	echo '</div>';
	
	include("embed-footer.php");
?>
		</div>
	</body>
</html>