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
	echo '<div id="title"><h1>Admin</h1></div>';
	echo '<div id="content">';
	if ($account && $account['admin']) {
		if (isset($_GET['a']) && $_GET['a']=='news') {
			$showform = true;
			if (isset($_POST['postnews']) && $_POST['postnews'] && isset($_POST['news']) && strlen($_POST['news'])>0) {
				$showform = false;
				if (mysql_query('INSERT INTO news (news,userid) VALUES ("'.mysql_real_escape_string(strip_tags($_POST['news'])).'",'.intval($_POST['userid']).')')) {
					showSuccessMessage('News posted.');
				} else {
					$showform = true;
					showErrorMessage('Failed to post news.');
				}
			}
			if ($showform) {
				echo '<form name="news" action="'.getUrl('admin-news').'" method="post">';
				echo '<input type="hidden" name="postnews" value="1" />';
				echo '<input type="hidden" name="userid" value="'.$account['id'].'" />';
				echo '<label><span class="name">As: '.$account['ndisplay'].'</span><span class="field"><input type="text" name="news"'.(isset($_POST['news'])?$_POST['news']:null).' /></span></label>';
				echo '<label><span class="field"><input type="submit" value="Post News" /></span></label>';
				echo '</form>';
			}
		} elseif (isset($_GET['a']) && $_GET['a']=='totals') {
			echo '<h2>Totals</h2>';
			$showform = true;
			if (isset($_POST['updateall']) && $_POST['updateall']) {
				$showform = false;
				$update = dbUpdateAllTotals();
				if ($update['u']==$update['t'] && $update['t']>0) {
					showSuccessMessage($update['u'].'/'.$update['t'].' user totals updated.');
				} else {
					showErrorMessage($update['u'].'/'.$update['t'].' user totals updated.');
				}
			}
			if (isset($_POST['updategame']) && $_POST['updategame'] && isset($_POST['game'])) {
				$showform = false;
				$update = dbUpdateAllTotals($_POST['game']);
				if ($update['u']==$update['t']) {
					showSuccessMessage($update['u'].'/'.$update['t'].' user totals updated.');
				} else {
					showErrorMessage($update['u'].'/'.$update['t'].' user totals updated.');
				}
			}
			if ($showform) {
				echo '<form name="totals" action="'.getUrl('admin-totals').'" method="post"><input type="hidden" name="updateall" value="1" /><input type="submit" value="Update All" /></form>';
				$games = dbGetAllGames();
				foreach ($games as $game) {
					echo '<form name="totals" action="'.getUrl('admin-totals').'" method="post"><input type="hidden" name="updategame" value="1" /><input type="hidden" name="game" value="'.$game['nurl'].'" /><input type="submit" value="Update '.$game['nshort'].'" /></form>';
				}
			}
		} elseif (isset($_GET['a']) && $_GET['a']=='users') {
			echo '<h2>Manage Users</h2>';
			$showform = true;
			if (isset($_POST['updateusers']) && intval($_POST['updateusers']) && isset($_POST['userid']) && is_array($_POST['userid'])) {
				$showform = false;
				if (isset($_POST['pass']) && validate_password($_POST['pass'],$account['password'])) {
					if (isset($_POST['confirm']) && intval($_POST['confirm'])) {
						foreach ($_POST['userid'] as $userid) {
							$user = dbGetUserById($userid);
							if ($user && $account['id']!=$user['id']) {
								if (mysql_query('DELETE FROM times WHERE userid='.$user['id'])) {
									showSuccessMessage('Removed all '.$user['ndisplay'].'\'s times.');
								} else {
									showErrorMessage('Failed to remove all of '.$user['ndisplay'].'\'s times. userid='.$user['id']);
								}
								if (mysql_query('DELETE FROM totals WHERE userid='.$user['id'])) {
									showSuccessMessage($user['ndisplay'].' removed from all game rankings.');
								} else {
									showErrorMessage('Failed to remove '.$user['ndisplay'].' from all game rankings. userid='.$user['id']);
								}
								if (mysql_query('DELETE FROM users WHERE id='.$user['id'])) {
									showSuccessMessage('User account '.$user['ndisplay'].' deleted.');
								} else {
									showErrorMessage('Failed to delete user account '.$user['ndisplay'].'. userid='.$user['id']);
								}
							}
						}
						if (dbUpdateAllTotals()) {
							showSuccessMessage('All point and time totals recalculated.');
						} else {
							showErrorMessage('Failed to recalculate all point and time totals.');
						}
					} else {
						echo '<form name="users" action="'.getUrl('admin-users').'" method="post">';
						echo '<p>You sure you wanna to do this?</p>';
						echo '<input type="hidden" name="updateusers" value="1" />';
						echo '<label><span class="field"><input type="checkbox" name="confirm" value="1" /><span class="check"></span><span class="checktext">Yup</span></span></label>';
						foreach ($_POST['userid'] as $userid) {
							if ($user = dbGetUserById($userid)) {
								echo $account['id']==$user['id']?null:'<label><span class="field"><input type="checkbox" name="userid[]" value="'.$user['id'].'" checked="checked" /><span class="check"></span><span class="checktext"><a href="'.getUrl('player','%PLAYER%',$user['nuser']).'">'.$user['ndisplay'].'</a></span></span></label>';
							}
						}
						echo '<input type="hidden" name="pass" value="'.$_POST['pass'].'" />';
						echo '<input type="submit" name="deleteusers" value="Delete Users" class="warning" />';
						echo '</form>';
					}
				} else {
					$showform = true;
					showErrorMessage('Password incorrect.');
				}
			}
			if ($showform) {
				$users = dbGetAllUsers();
				echo '<form name="users" action="'.getUrl('admin-users').'" method="post">';
				echo '<input type="hidden" name="updateusers" value="1" />';
				foreach ($users as $user) {
					echo $account['id']==$user['id']?null:'<label><span class="field"><input type="checkbox" name="userid[]" value="'.$user['id'].'" /><span class="check"></span><span class="checktext"><a href="'.getUrl('player','%PLAYER%',$user['nuser']).'">'.$user['ndisplay'].'</a></span></span></label>';
				}
				echo '<label><span class="name">Password</span><span class="field"><input type="password" name="pass" /></span></label>';
				echo '<label><span class="field"><input type="submit" name="deleteusers" value="Delete Users" class="warning" /></span></label>';
				echo '</form>';
			}
		} else {
			//news
			echo '<h2>Post News</h2>';
			echo '<form name="news" action="'.getUrl('admin-news').'" method="post">';
			echo '<input type="hidden" name="postnews" value="1" /><input type="hidden" name="userid" value="'.$account['id'].'" />';
			echo '<label><span class="name">As: '.$account['ndisplay'].'</span><span class="field"><input type="text" name="news" /></span></label>';
			echo '<label><span class="field"><input type="submit" value="Post News" /></span></label>';
			echo '</form>';
			//totals
			echo '<h2>Update Totals</h2>';
			echo '<form name="totals" action="'.getUrl('admin-totals').'" method="post"><input type="hidden" name="updateall" value="1" /><input type="submit" value="Update All" /></form>';
			$games = dbGetAllGames();
			foreach ($games as $game) {
				echo '<form name="totals" action="'.getUrl('admin-totals').'" method="post"><input type="hidden" name="updategame" value="1" /><input type="hidden" name="game" value="'.$game['nurl'].'" /><input type="submit" value="Update '.$game['nshort'].'" /></form>';
			}
			//users
			echo '<h2>Manage Users</h2>';
			$users = dbGetAllUsers();
			echo '<form name="users" action="'.getUrl('admin-users').'" method="post">';
			echo '<p><a href="'.getUrl('signup').'" class="button">Create User</a></p>';
			echo '<input type="hidden" name="updateusers" value="1" />';
			foreach ($users as $user) {
				echo $account['id']==$user['id']?null:'<label><span class="field"><input type="checkbox" name="userid[]" value="'.$user['id'].'" /><span class="check"></span><span class="checktext"><a href="'.getUrl('player','%PLAYER%',$user['nuser']).'">'.$user['ndisplay'].'</a></span></span></label>';
			}
			echo '<label><span class="name">Password</span><span class="field"><input type="password" name="pass" /></span></label>';
			echo '<label><span class="field"><input type="submit" name="deleteusers" value="Delete Users" class="warning" /></span></label>';
			echo '</form>';
			//security
			echo '<h2>Password Key Stretching</h2>';
			$pw = 'test';
			$hash = create_hash($pw);
			$mt1 = microtime(true);
			validate_password($pw,$hash);
			$mt2 = microtime(true);
			echo PBKDF2_ITERATIONS.' iterations = '.round($mt2-$mt1,3).'s';
			
		}
	} else {
		showErrorMessage('You do not have permission to view this page');
	}
	echo '</div>';
	echo '</div>';
	
	include("embed-footer.php");
?>
		</div>
	</body>
</html>