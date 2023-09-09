<?php
	//functions
	include_once("lib/functions.php");
	include_once("lib/recaptcha.php");
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
	echo '<div id="title"><h1>Sign Up</h1></div>';
	echo '<div id="content">';
	
	//check if logged in
	$showform = false;
	$account = isset($_COOKIE['login'])?dbLoggedIn($_COOKIE['login']):false;
	if ($account && !$account['admin']) {
		echo '<p>You already have an account.</p>';
	} elseif (isset($_POST['validate'])) {
		//validate username
		if (isset($_POST['user']) && !preg_match('/'.$_VALIDATION['urlplayer'].'/',strtolower($_POST['user']))) {
			$showform = true;
			showErrorMessage('Username invalid.');
		}
		//check username availability
		if (isset($_POST['user']) && preg_match('/'.$_VALIDATION['urlplayer'].'/',strtolower($_POST['user'])) && mysql_num_rows(mysql_query('SELECT id FROM users WHERE nuser="'.strtolower($_POST['user']).'"'))>0) {
			$showform = true;
			showErrorMessage('Username taken.');
		}
		//validate password
		if (isset($_POST['pass1']) && isset($_POST['pass2']) && !(($_POST['pass1']==$_POST['pass2'] && strlen($_POST['pass1'])>0) || ($account && $account['admin'] && strlen($_POST['pass1'].$_POST['pass2'])==0))) {
			$showform = true;
			showErrorMessage('Passwords do not match.');
		}
		//validate email
		if (isset($_POST['email1']) && isset($_POST['email2']) && !((preg_match('/'.$_VALIDATION['email'].'/',$_POST['email1']) && $_POST['email1']==$_POST['email2']) || ($account && $account['admin'] && strlen($_POST['email1'].$_POST['email2'])==0))) {
			$showform = true;
			showErrorMessage('E-Mail invalid or does not match.');
		}
		//check recaptcha results
		if (isset($_POST['recaptcha_challenge_field']) && isset($_POST['recaptcha_response_field'])) {
			$recaptcha = recaptcha_check_answer($_SETTINGGLOBAL['recaptcha-key-private'],$_SERVER["REMOTE_ADDR"],$_POST["recaptcha_challenge_field"],$_POST["recaptcha_response_field"]);
			if (!$recaptcha->is_valid) {
				$showform = true;
				showErrorMessage('Failed reCAPTCHA test.');
			}
		} else {
			$showform = true;
			showErrorMessage('Failed reCAPTCHA test.');
		}
		//process data
		if (!$showform && isset($_POST['user']) && isset($_POST['pass1']) && isset($_POST['email1'])) {
			$newuser = mysql_query('INSERT INTO users (nuser,ndisplay,email,password) VALUES ("'.strtolower($_POST['user']).'","'.$_POST['user'].'","'.$_POST['email1'].'","'.($account&&$account['admin']&&strlen($_POST['pass1']==0)?'':create_hash($_POST['pass1'])).'")');
			if ($newuser) {
				if ($account&&$account['admin']) {
					showSuccessMessage('Account successfully created.');
				} else {
					echo '<p>Thanks for signing up, '.$_POST['user'].'! You will be able to edit your profile and join the rankings shortly.</p>';
					$token = dbCreateNewSessionToken();
					$user = mysql_fetch_array(mysql_query('SELECT id FROM users WHERE nuser="'.strtolower($_POST['user']).'"'));
					if (mysql_query('INSERT INTO sessions (token,userid,type,expires) VALUES ("'.$token.'",'.$user['id'].',"verify",'.$_SETTINGGLOBAL['verification-limit'].')')) {
						if (sendMail($_POST['email1'],'Account Verification','<p>Thanks again for signing up, '.$_POST['user'].'!</p>'."\r\n\r\n".'<p>Before you\'ll be able to sign in and use your account, you will need to go to the following link: <a href="'.getUrl('verify-link','%TOKEN%',rawurlencode($token)).'">'.getUrl('verify-link','%TOKEN%',$token).'</a></p>'."\r\n\r\n".'<p>The link expires in '.$_SETTINGGLOBAL['verification-text'].'. If you need to generate a new one, you can do so here: <a href="'.getUrl('verify').'">'.getUrl('verify').'</a></p>'."\r\n\r\n".'<p>We will watch your career with great interest.</p>')) {
							echo '<p>A verification link has been sent to your e-mail address and will expire in '.$_SETTINGGLOBAL['verification-text'].'. Once your account is verified you will be able to log in. If you need to generate a new verification link, please go to <a href="'.getUrl('verify').'">this page</a>.</p>';
						} else {
							echo '<p>Unfortunately, the e-mail containing your verification link failed to send. Please visit <a href="'.getUrl('verify').'">this page</a> to generate a new one and try again.</p>';
						}
					} else {
						echo '<p>Unfortunately, an error was encountered while trying to generate a verification link. Please visit <a href="'.getUrl('verify').'">this page</a> to generate a new one and try again.</p>';
					}
				}
			}
		}
	} else {
		$showform = true;
	}
	if ($showform) {
		echo '<form name="signup" action="'.getUrl('signup').'" method="post"><input type="hidden" name="validate" value="1" />';
		echo '<label><span class="name">Username<span class="note">May only contain these characters: <b>a-z</b>, <b>0-9</b>, <b>_</b>, <b>-</b></span></span><span class="field"><input type="text" name="user" value="'.(isset($_POST['user'])?$_POST['user']:'').'" /></span></label>';
		echo '<label><span class="name">Password<br/></span><span class="field"><input type="password" name="pass1" value="'.(isset($_POST['pass1'])?$_POST['pass1']:'').'" /></span></label>';
		echo '<label><span class="name"><span class="note">Confirm password</span></span><span class="field"><input type="password" name="pass2" value="'.(isset($_POST['pass2'])?$_POST['pass2']:'').'" /></span></label>';
		echo '<label><span class="name">E-Mail<br/><span class="note">Required for account verification. Will NOT be displayed publicly, but staff may use it to contact you.</span></span><span class="field"><input type="text" name="email1" value="'.(isset($_POST['email1'])?$_POST['email1']:'').'" /></span></label>';
		echo '<label><span class="name"><span class="note">Confirm e-mail</span></span><span class="field"><input type="text" name="email2" value="'.(isset($_POST['email2'])?$_POST['email2']:'').'" /></span></label>';
		echo '<label><span class="name">Prove you\'re human</span><span class="field">'.recaptcha_get_html($_SETTINGGLOBAL['recaptcha-key-public']).'</span></label>';
		echo '<label><span class="field"><input type="submit" value="Sign Up" /></span></label>';
		echo '</form>';
	}
	echo '</div>';
	echo '</div>';
	
	include("embed-footer.php");
?>
		</div>
	</body>
</html>