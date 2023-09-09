<?php
	//functions
	include_once("lib/functions.php");
	include_once("lib/pbkdf2.php");
	//mysql init
	include_once("lib/connect.php");
	//data
	include_once("data/validation.php");
	include_once("data/settings.php");
?>	
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<?php //echo $success||$loggedin?'<meta http-equiv="refresh" content="'.$_SETTINGGLOBAL['redirect-delay'].'; url='.$_SETTINGGLOBAL['url'].$_URI['home'].'" />':''; ?>
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
	echo '<div id="title"><h1>Verify Account</h1></div>';
	echo '<div id="content">';
	
	if (isset($_GET['t'])) {
		//process token
		//echo urldecode($_GET['t']);
		if (preg_match('/'.$_VALIDATION['base64'].'/',rawurldecode($_GET['t']))) {
			$token = mysql_query('SELECT * FROM sessions WHERE token="'.rawurldecode($_GET['t']).'"');
			if (mysql_num_rows($token)>0) {
				$token = mysql_fetch_array($token);
				if (mysql_query('UPDATE users SET verified=1 WHERE id='.$token['userid'])) {
					$user = mysql_fetch_array(mysql_query('SELECT * FROM users WHERE id='.$token['userid']));
					mysql_query('DELETE FROM sessions WHERE token="'.$token['token'].'"');
					echo '<p>Your account is now verified, '.$user['ndisplay'].'! You can now <a href="'.getUrl('login').'">log in</a>, edit your profile and join the rankings.</p>';
				} else {
					showErrorMessage('Updating user account failed. Please <a href="'.getUrl('verify').'">try another token</a> or <a href="'.getUrl('contact').'">contact a staff member</a>.');
				}
			} else {
				showErrorMessage('Token doesn\'t exist.');
			}
		} else {
			showErrorMessage('Invalid token.');
		}
	} elseif (isset($_POST['validate']) && isset($_POST['user']) && isset($_POST['pass'])) {
		//process form
		if (preg_match('/'.$_VALIDATION['urlplayer'].'/',strtolower($_POST['user']))) {
			$user = mysql_fetch_array(mysql_query('SELECT * FROM users WHERE nuser="'.strtolower($_POST['user']).'"'));
			if ($user && validate_password($_POST['pass'],$user['password'])) {
				if (!$user['verified']) {
					$token = dbCreateNewSessionToken();
					if (mysql_query('REPLACE INTO sessions (userid,type,token,created,expires) VALUES ('.$user['id'].',"verify","'.$token.'",NOW(),'.$_SETTINGGLOBAL['verification-limit'].')')) {
						if (sendMail($user['email'],'Account Verification','<p>Thanks again for signing up, '.$_POST['user'].'!</p>'."\r\n\r\n".'<p>Before you\'ll be able to sign in and use your account, you will need to go to the following link: <a href="'.getUrl('verify-link','%TOKEN%',urlencode($token)).'">'.getUrl('verify-link','%TOKEN%',rawurlencode($token)).'</a></p>'."\r\n\r\n".'<p>The link expires in '.$_SETTINGGLOBAL['verification-text'].'. If you need to generate a new one, you can do so here: <a href="'.getUrl('verify').'">'.getUrl('verify').'</a></p>'."\r\n\r\n".'<p>We will watch your career with great interest.</p>')) {
							echo '<p>A verification link has been sent to your e-mail address and will expire in '.$_SETTINGGLOBAL['verification-text'].'.</p>';
						} else {
							showErrorMessage('Verification e-mail failed to send. Please try again.');
						}
					} else {
						showErrorMessage('New verification token creation failed. Please try again.');
					}
				} else {
					showErrorMessage('Username already verified.');
				}
			} else {
				showErrorMessage('Incorrect credentials.');
			}
		} else {
			showErrorMessage('Invalid username.');
		}
	} else {
		//new verification form
		echo '<form name="verify" action="'.getUrl('verify').'" method="post"><input type="hidden" name="validate" value="1" />';
		echo '<p>Fill out this form to send a new verification email for your account.</p>';
		echo '<label><span class="name">Username</span><span class="field"><input type="text" name="user" /></span></label>';
		echo '<label><span class="name">Password</span><span class="field"><input type="password" name="pass" /></span></label>';
		echo '<label><span class="field"><input type="submit" value="Send Verification E-Mail" /></span></label>';
		echo '</form>';
	}
	echo '</div>';
	echo '</div>';
	
	include("embed-footer.php");
?>
		</div>
	</body>
</html>