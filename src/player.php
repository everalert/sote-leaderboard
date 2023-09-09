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
	include("embed-menuplayer.php");
	
	
	
	
	//get tags
	$tagList = dbGetTags();
	$account = dbLoggedIn();
	
	//start
	echo '<div id="page">';
	if (isset($_GET['p'])) {
		//check game is valid
		$player = dbGetUser($_GET['p']);
		if ($player!=false) {
			if (isset($_GET['g'])) {
				//check game is valid
				$game = dbGetGame($_GET['g']);
				if ($game) {
					
					
					
					
					
					//PLAYER GAME HISTORY
					if (isset($_GET['a']) && $_GET['a']=='history') {
						//pagination data
						$hSize = mysql_query('SELECT * FROM times WHERE userid='.$player['id'].' ORDER BY played DESC, treal ASC, tgame ASC') or die ('<p>'.mysql_error().'</p>');
						$hSize = mysql_num_rows($hSize);
						$page = isset($_GET['pg'])&&is_numeric($_GET['pg'])&&$_GET['pg']>=1?floor($_GET['pg']-1):0;
						$pageLast = floor($hSize/$_SETTINGGLOBAL['player-history-pagesize']);
						$pageStr = '<div class="pagination">';
						$pageStr .= $page<=0?'<span class="blank end">&laquo;</span>':'<a href="'.$_SETTINGGLOBAL['url'].str_replace(array('%PLAYER%','%GAME%'),array($player['nuser'],$game['nurl']),$_URI['player-game-history']).'" class="button end">&laquo;</a>';
						$pageStr .= $page<=0?'<span class="blank mid">&nbsp;</span>':'<a href="'.$_SETTINGGLOBAL['url'].str_replace(array('%PLAYER%','%GAME%','%PAGE%'),array($player['nuser'],$game['nurl'],$page),$_URI['player-game-history-page']).'" class="button mid">'.$page.'</a>';
						$pageStr .= '<span class="blank mid">'.($page+1).'</span>';
						$pageStr .= $page>=$pageLast?'<span class="blank mid">&nbsp;</span>':'<a href="'.$_SETTINGGLOBAL['url'].str_replace(array('%PLAYER%','%GAME%','%PAGE%'),array($player['nuser'],$game['nurl'],$page+2),$_URI['player-game-history-page']).'" class="button mid">'.($page+2).'</a>';
						$pageStr .= $page>=$pageLast?'<span class="blank end">&raquo;</span>':'<a href="'.$_SETTINGGLOBAL['url'].str_replace(array('%PLAYER%','%GAME%','%PAGE%'),array($player['nuser'],$game['nurl'],$pageLast+1),$_URI['player-game-history-page']).'" class="button end">&raquo;</a>';
						$pageStr .= '</div>';
						//output headings
						echo '<div id="title"><h1>'.$player['ndisplay'].' &mdash; '.$game['nshort'].' Time History</h1></div>';
						echo '<div id="content"><div id="player-gamehistory">';
						echo $pageStr;
						echo '<div class="timelist"><table>';
						echo '<tr class="head"><td class="cat">Category</td><td class="time">Time</td><td class="best">Best</td><td class="ver">Version</td><td class="date">Date</td></tr>';
						//output times
						$history = mysql_query('SELECT * FROM times WHERE userid='.$player['id'].' ORDER BY played DESC, treal ASC, tgame ASC LIMIT '.$page*$_SETTINGGLOBAL['player-history-pagesize'].','.$_SETTINGGLOBAL['player-history-pagesize']) or die ('<p>'.mysql_error().'</p>');
						while ($hTime = mysql_fetch_array($history)) {
							//get ranking data
							$ranking = dbGetRankingById($hTime['rankingid']);
							//set time type
							if ($ranking['timetype']=="tREAL"||$ranking['timetype']=="tRLONLY") {
								$t1 = "treal";
								$t2 = "tgame";
							} else {
								$t1 = "tgame";
								$t2 = "treal";
							}
							//set time string
							$uStrT = $_SETTINGGLOBAL['url'].str_replace('%TIME%',$hTime['id'],$_URI['time']);
							$tStrTime1 = sToTime($hTime[$t1]);
							$tStrTime2 = sToTime($hTime[$t2]);
							$tStr1 = '<a href="'.$uStrT.'">'.$tStrTime1.'</a>';
							if ($ranking['timetype']=='tGMONLY'||$ranking['timetype']=='tRLONLY') {
								$tStr2 = '';
							} else {
								$tStr2 = '<span class="subtime">('.$tStrTime2.')</span>';
							}
							//get best comparable time
							$best = mysql_query('SELECT * FROM times WHERE rankingid='.$hTime['rankingid'].' AND userid='.$hTime['userid'].' AND difficulty="'.$hTime['difficulty'].'" ORDER BY '.$t1.','.$t2.' ASC LIMIT 1') or die('<p>'.mysql_error().'</p>');
							$best = mysql_fetch_array($best);
							//output row
							echo '<tr class="row">';
							echo '<td class="cat"><a href="'.getUrl('ranking',array('%GAME%','%RANKING%'),array($game['nurl'],$ranking['nurl'])).'">'.$ranking['nfull'].'</a> &mdash; <span class="'.$hTime['difficulty'].'">'.$tagList[$hTime['difficulty']]['name'].'</span></td>';
							echo '<td class="time">'.$tStr1.$tStr2.'</td>';
							echo '<td class="best bst'.($best['id']==$hTime['id']?1:0).'">'.($best['id']==$hTime['id']?'YES':'NO').'</td>';
							echo '<td class="ver">'.$tagList[$hTime['version']]['name'].'</td>';
							echo '<td class="date">'.strftime($_SETTINGGLOBAL['date-format'],strtotime($hTime['played'])).'</td>';
							echo '</tr>';
						}
						echo '</table></div>';
						echo $pageStr;
						echo '</div></div>';
					
					
					
					
					
					//PLAYER GAME COMPARISON
					//} elseif (isset($_GET['a']) && $_GET['a']=='compare') {
						
						
						
						
						
					//PLAYER GAME TIMES
					} else {
						echo '<div id="title"><h1>'.$player['ndisplay'].' &mdash; Best '.$game['nshort'].' Times</h1></div>';
						echo '<div id="content">';
						echo '<div id="player-timesheet">';
						echo '<p>'.$player['ndisplay'].'\'s best known times for '.$game['nfull'].'.</p>';
						echo '<div class="timelist"><table>';
						echo '<tr class="head"><td rowspan="2" class="cat"></td>';
						$totalTime = array();
						$totalPoints = 0;
						foreach (explode(" ",$game['difficulties']) as $difficulty) {
							echo '<td colspan="3" class="dif '.$difficulty.'">'.$tagList[$difficulty]['name'].'</td>';
							$totalTime[$difficulty] = 0;
						}
						echo '</tr>';
						echo '<tr class="head">';
						foreach (explode(" ",$game['difficulties']) as $difficulty) {
							echo '<td class="time">Time</td><td class="rank">Rank</td><td class="pts">Pts</td>';
						}
						echo '</tr>';
						$rankings = dbGetAllRankings($game['nurl']);
						//go through each ranking one by one
						foreach ($rankings as $ranking) {
							//calc points
							$points = explode(",",$game['pointscale']);
							for ($a=0;$a<count($points);$a++) {
								$points[$a] = floor($ranking['maxpoints']*$points[$a]/100);
							}
							//set time type
							if ($ranking['timetype']=="tREAL"||$ranking['timetype']=="tRLONLY") {
								$t1 = "treal";
								$t2 = "tgame";
							} else {
								$t1 = "tgame";
								$t2 = "treal";
							}
							//start
							$uStrR = $_SETTINGGLOBAL['url'].str_replace(array('%GAME%','%RANKING%'),array($game['nurl'],$ranking['nurl']),$_URI['ranking']);
							$iStr = strlen($ranking['imgicon'])>0?'<img src="'.$ranking['imgicon'].'" title="'.$ranking['nfull'].'" /> ':'';
							echo '<tr class="row"><td class="cat"><a href="'.$uStrR.'">'.$iStr.$ranking['nfull'].'</a></td>';					
							//go through each difficulty
							foreach (explode(" ",$game['difficulties']) as $difficulty) {
								//get time data
								$qDif = $difficulty!="dALL"?" AND difficulty='".$difficulty."'":"";
								$qRankExt = $ranking['piggyback']>0?' OR rankingid='.$ranking['piggyback']:'';
								$query = 'SELECT * FROM (SELECT *,(@rnk:=@rnk+1)rnk,(@rank:=IF(@ct1=treal AND @ct2=tgame,@rank,@rnk))rank,(@ct1:=treal)nt1,(@ct2:=tgame)nt2 FROM (SELECT times.* FROM times JOIN (SELECT userid,MIN('.$t1.')'.$t1.' FROM times WHERE (rankingid='.$ranking['id'].$qRankExt.')'.$qDif.' GROUP BY userid)fastest ON (fastest.userid = times.userid AND fastest.'.$t1.' = times.'.$t1.') WHERE (rankingid='.$ranking['id'].$qRankExt.')'.$qDif.' ORDER BY '.$t1.','.$t2.',played,id ASC)times)times WHERE userid='.$player['id'].' ORDER BY rank,played,id ASC LIMIT 1';
								//echo '<p>'.$query.'</p>';
								foreach (explode(";","SET @rnk=0;SET @rank=0;SET @ct1=0;SET @ct2=0") as $subquery) {
									mysql_query($subquery) or die('<p>'.mysql_error().'</p>');
								}
								$times = mysql_query($query) or die('<p>'.mysql_error().'</p>');
								//paybay
								$topTime = mysql_fetch_array($times);
								$ranked = preg_match('/'.str_replace('%TAG%',$difficulty,$_VALIDATION['tagexists']).'/',$ranking['difficulties'])?true:false;
								if ($topTime!=false) {
									$totalTime[$difficulty]+=$topTime[$t1];
									$pts = preg_match('/'.str_replace('%TAG%',$difficulty,$_VALIDATION['tagexists']).'/',$ranking['difficulties'])?$points[min($topTime['rank']-1,count($points)-1)]:0;
									$totalPoints += $pts;
									//time formatting
									$uStrT = $_SETTINGGLOBAL['url'].str_replace('%TIME%',$topTime['id'],$_URI['time']);
									$tStrTime1 = sToTime($topTime[$t1]);
									$tStrTime2 = sToTime($topTime[$t2]);
									$tStr1 = '<a href="'.$uStrT.'">'.$tStrTime1.'</a>';
									if ($ranking['timetype']=='tGMONLY'||$ranking['timetype']=='tRLONLY') {
										$tStr2 = '';
									} else {
										$tStr2 = '<span class="subtime">('.$tStrTime2.')</span>';
									}
									//get user data
									$user = mysql_query('SELECT * FROM users WHERE id="'.$topTime['userid'].'" LIMIT 1');
									$user = mysql_fetch_array($user);
									$uStrP = $_SETTINGGLOBAL['url'].str_replace('%PLAYER%',$user['nuser'],$_URI['player']);
									//final output for cell
									echo '<td class="time'.($ranked?null:' unranked').'">'.$tStr1.$tStr2.'</td><td class="rank rnk'.$topTime['rank'].($ranked?null:' unranked').'">'.nToRank($topTime['rank']).'</td><td class="pts'.($ranked?null:' unranked').'">'.$pts.'</td>';
									
								} else {
									echo '<td colspan="3" class="none'.($ranked?null:' unranked').'">No Times</td>';
									$totalTime[$difficulty]+=$_SETTING[$game['nurl']]['deftime'];
								}
							}
							echo '</tr>';
						}
						echo '<tr class="head"><td class="totals">Time Totals</td>';
						foreach (explode(" ",$game['difficulties']) as $difficulty) {
							echo '<td colspan="3" class="total">'.sToTime($totalTime[$difficulty]).'</td>';
						}
						echo '</tr>';
						echo '</table>';
						echo '</div>';
						$totalTimeKeys = array_keys($totalTime);
						$tTime = 0;
						foreach ($totalTimeKeys as $key) {
							if ($key!="dALL") {  $tTime += $totalTime[$key];  }
						}
						echo '<div class="timesheet-points"><h2>Total Points</h2><span class="points">'.$totalPoints.'<span class="suffix">pts</span></span></div>';
						echo '<div class="timesheet-time"><h2>Total Time</h2><span class="time">'.sToTime($tTime).'</span><span class="note">(Excludes "'.$tagList['dALL']['name'].'")</span></div>';
						echo '</div>';
					}
				} else {
					echo '<p>Invalid game specified. <a href="'.$_SETTINGGLOBAL['url'].str_replace('%PLAYER%',$player['nuser'],$_URI['player']).'">Please select a proper game</a>.</p>';
				}
			} else {
				//MAIN PROFILE
				$showprofile = true;
				if (isset($_GET['a']) && $_GET['a']=='edit') {
					if ($account && ($account['admin']||$account['id']==$player['id'])) {
						$showform = true;
						$showprofile = false;
						if (isset($_POST['editprofile']) && intval($_POST['editprofile'])) {
							$showform = false;
							//validate username
							if (isset($_POST['user']) && $_POST['user']==$player['nuser']) {
								$showform = true;
								showErrorMessage('Username invalid.');
							}
							//validate new password
							if (isset($_POST['newpasscheck']) && intval($_POST['newpasscheck']) && isset($_POST['newpass1']) && isset($_POST['newpass2']) && !(($_POST['newpass1']==$_POST['newpass2'] && strlen($_POST['newpass1'])>0) || ($account['admin'] && strlen($_POST['newpass1'].$_POST['newpass2'])==0))) {
								$showform = true;
								showErrorMessage('Passwords do not match.');
							}
							//validate avatar
							if (isset($_POST['avatar']) && !preg_match('!'.$_VALIDATION['avatar'].'!',$_POST['avatar']) && strlen($_POST['avatar'])>0) {
								$showform = true;
								showErrorMessage('Avatar URL invalid.');
							}
							//validate youtube
							if (isset($_POST['ytid']) && !preg_match('!'.$_VALIDATION['youtube'].'!',$_POST['ytid']) && strlen($_POST['ytid'])>0) {
								$showform = true;
								showErrorMessage('YouTube username invalid.');
							}
							//validate twitch
							if (isset($_POST['ttvid']) && !preg_match('!'.$_VALIDATION['twitch'].'!',$_POST['ttvid']) && strlen($_POST['ttvid'])>0) {
								$showform = true;
								showErrorMessage('Twitch username invalid.');
							}
							//validate twitter
							if (isset($_POST['twid']) && !preg_match('!'.$_VALIDATION['twitter'].'!',$_POST['twid']) && strlen($_POST['twid'])>0) {
								$showform = true;
								showErrorMessage('Twitter username invalid.');
							}
							//validate birthdate
							if (isset($_POST['bdayY']) && isset($_POST['bdayM']) && isset($_POST['bdayD']) && !(intval($_POST['bdayY'])+intval($_POST['bdayM'])+intval($_POST['bdayD'])==0 || (intval($_POST['bdayY']) && intval($_POST['bdayM']) && intval($_POST['bdayD'])))) {
								$showform = true;
								showErrorMessage('Birthday invalid.');
							}
							//validate password
							if (!(validate_password($_POST['pass'],$player['password'])||(validate_password($_POST['pass'],$account['password'])&&$account['admin']))) {
								$showform = true;
								showErrorMessage('Incorrect password.');
							}
							//process changes
							if (!$showform) {
								$newpass = isset($_POST['newpasscheck'])&&intval($_POST['newpasscheck'])?($account['admin']&&strlen($_POST['newpass1']==0)?'':create_hash($_POST['newpass1'])):$player['password'];
								$birth = new DateTime(intval($_POST['bdayY']).'-'.intval($_POST['bdayM']).'-'.intval($_POST['bdayD'])); //$birth->format('Y-m-d')
								$birthout = intval($_POST['bdayY'])+intval($_POST['bdayM'])+intval($_POST['bdayD'])==0?'0000-00-00 00:00:00':$birth->format('Y-m-d').' 00:00:00';
								if (mysql_query('UPDATE users SET ndisplay="'.mysql_real_escape_string(strip_tags($_POST['ndisplay'])).'", nreal="'.mysql_real_escape_string(strip_tags($_POST['nreal'])).'", password="'.$newpass.'", avatar="'.$_POST['avatar'].'", ytid="'.$_POST['ytid'].'", ttvid="'.$_POST['ttvid'].'", twid="'.$_POST['twid'].'", location="'.mysql_real_escape_string(strip_tags($_POST['location'])).'", birthdate="'.$birthout.'", bio="'.mysql_real_escape_string(strip_tags($_POST['bio'])).'" WHERE nuser="'.$_POST['nuser'].'"')) {
									$showprofile = true;
									showSuccessMessage('Profile updated successfully.');
								} else {
									$showform = true;
									showErrorMessage('Update failed. Please try again in a moment.');
								}
							}
						}
						if (isset($_POST['editranking']) && intval($_POST['editranking'])) {
							$showform = false;
							//validate username
							if (isset($_POST['user']) && $_POST['user']==$player['nuser']) {
								$showform = true;
								showErrorMessage('Username invalid.');
							}
							//validate game 
							if (isset($_POST['ngame']) && !$game = dbGetGame($_POST['ngame'])) {
								$showform = true;
								showErrorMessage('Game invalid.');
							}
							//validate action
							if (!(isset($_POST['join']) && intval($_POST['join'])) && !isset($_POST['vpref'])) {
								$showform = true;
								showErrorMessage('Action invalid.');
							}
							//validate vpref
							if (isset($_POST['vpref']) && !(array_key_exists($_POST['vpref'],$tagList)||$_POST['vpref']=='')) {
								$showform = true;
								showErrorMessage('Version preference invalid.');
							}
							//process changes
							if (!$showform) {
								if (isset($_POST['join'])) {
									$t = dbCalculateTotals($player['nuser'],$game['nurl']);
									if (mysql_query('INSERT INTO totals (userid,gameid,points,time) VALUES ('.$player['id'].','.$game['id'].','.$t['p'].','.$t['tr'].')')) {
										$showprofile = true;
										showSuccessMessage('Rankings joined successfully.');
									} else {
										$showform = true;
										showErrorMessage('Could not join rankings. Please try again in a moment.');
									}
								}
								if (isset($_POST['vpref'])) {
									if (mysql_query('UPDATE totals SET vpref="'.$_POST['vpref'].'" WHERE userid='.$player['id'].' AND gameid='.$game['id'])) {
										$showprofile = true;
										showSuccessMessage('Rankings updated successfully.');
									} else {
										$showform = true;
										showErrorMessage('Update failed. Please try again in a moment.');
									}
								}
							}
						}
						if ($showform) {
							echo '<div id="title"><h1>Edit '.$player['ndisplay'].'</h1></div>';
							echo '<div id="content">';
							echo '<h2>Edit Profile</h2>';
							echo '<form name="profile" action="'.getUrl('player-edit','%PLAYER%',$player['nuser']).'" method="post"><input type="hidden" name="editprofile" value="1" /><input type="hidden" name="nuser" value="'.(isset($_POST['nuser'])?$_POST['nuser']:$player['nuser']).'" />';
							echo '<label><span class="name">Display Name</span><span class="field"><input type="text" name="ndisplay" value="'.(isset($_POST['ndisplay'])?$_POST['ndisplay']:$player['ndisplay']).'" /></span></label>';
							echo '<label><span class="name">Real Name</span><span class="field"><input type="text" name="nreal" value="'.(isset($_POST['nreal'])?$_POST['nreal']:$player['nreal']).'" /></span></label>';
							echo '<label><span class="field"><input type="checkbox" name="newpasscheck" value="1"'.(isset($_POST['newpasscheck'])&&intval($_POST['newpasscheck'])?' selected="selected"':'').' /><span class="check"></span><span class="checktext">Change Password</span></span></label>';
							echo '<label><span class="name">New Password</span><span class="field"><input type="password" name="newpass1" /></span></label>';
							echo '<label><span class="name"><span class="note">Confirm new password</span></span><span class="field"><input type="password" name="newpass2" /></span></label>';
							echo '<label><span class="name">Avatar<span class="note">URL of a small JPG, JPEG, PNG or GIF image.</span></span><span class="field"><input type="text" name="avatar" value="'.(isset($_POST['avatar'])?$_POST['avatar']:$player['avatar']).'" /></span></label>';
							echo '<label><span class="name">Twitch</span><span class="field"><input type="text" name="ttvid" value="'.(isset($_POST['ttvid'])?$_POST['ttvid']:$player['ttvid']).'" /></span></label>';
							echo '<label><span class="name">YouTube</span><span class="field"><input type="text" name="ytid" value="'.(isset($_POST['ytid'])?$_POST['ytid']:$player['ytid']).'" /></span></label>';
							echo '<label><span class="name">Twitter</span><span class="field"><input type="text" name="twid" value="'.(isset($_POST['twid'])?$_POST['twid']:$player['twid']).'" /></span></label>';
							echo '<label><span class="name">Location</span><span class="field"><input type="text" name="location" value="'.(isset($_POST['location'])?$_POST['location']:$player['location']).'" /></span></label>';
							if ($player['birthdate']!='0000-00-00 00:00:00') {
								$bday = new DateTime($player['birthdate']);
								echo '<div class="label"><span class="name">Birthday<span class="note">0000/00/00 to not display age</span></span><span class="field">Y <input type="text" name="bdayY" value="'.(isset($_POST['bdayY'])?$_POST['bdayY']:$bday->format('Y')).'" />/<input type="text" name="bdayM" value="'.(isset($_POST['bdayM'])?$_POST['bdayM']:$bday->format('m')).'" />/<input type="text" name="bdayD" value="'.(isset($_POST['bdayD'])?$_POST['bdayD']:$bday->format('d')).'" /></span></div>';
							} else {
								echo '<div class="label"><span class="name">Birthday<span class="note">0000/00/00 to not display age</span></span><span class="field"><input type="text" name="bdayY" value="'.(isset($_POST['bdayY'])?$_POST['bdayY']:'0000').'" />/<input type="text" name="bdayM" value="'.(isset($_POST['bdayM'])?$_POST['bdayM']:'00').'" />/<input type="text" name="bdayD" value="'.(isset($_POST['bdayD'])?$_POST['bdayD']:'00').'" /></span></div>';
							}
							echo '<label><span class="name">Bio</span><span class="field"><textarea name="bio">'.(isset($_POST['bio'])?$_POST['bio']:$player['bio']).'</textarea></span></label>';
							echo '<label><span class="name">Password<span class="note">Verify '.($account['admin']&&$account['id']!=$player['id']?'admin':'your').' password</span></span><span class="field"><input type="password" name="pass" /></span></label>';
							echo '<label><span class="field"><input type="submit" value="Edit Profile" /></span></label>';
							echo '</form>';
							$games = dbGetAllGames();
							foreach ($games as $game) {
								echo '<h2>Edit Ranking: '.$game['nfull'].'</h2>';
								if ($total = dbGetTotal($player['nuser'],$game['nurl'])) {
									echo '<form name="ranking" action="'.getUrl('player-edit','%PLAYER%',$player['nuser']).'" method="post">';
									echo '<input type="hidden" name="editranking" value="1" />';
									echo '<input type="hidden" name="nuser" value="'.(isset($_POST['nuser'])?$_POST['nuser']:$player['nuser']).'" />';
									echo '<input type="hidden" name="ngame" value="'.(isset($_POST['ngame'])?$_POST['ngame']:$game['nurl']).'" />';
									$vpref = isset($_POST['vpref'])?$_POST['vpref']:$total['vpref'];
									echo '<label><span class="name">Version Preference</span><span class="field"><span class="select-wrapper"><select name="vpref">';
									echo '<option value=""'.($vpref==''?' selected="selected"':null).'>No Preference</option>';
									foreach (explode(' ',$game['versions']) as $ver) {
										echo '<option value="'.$ver.'"'.($ver==$vpref?' selected="selected"':null).'>'.$tagList[$ver]['name'].'</option>';
									}
									echo '</select></span></span></label>';
									echo '<label><span class="field"><input type="submit" value="Edit Ranking" /></span></label>';
									echo '</form>';
								} else {
									echo '<form name="profile" action="'.getUrl('player-edit','%PLAYER%',$player['nuser']).'" method="post">';
									echo '<p>'.$game['nfull'].' Rankings have not been joined.</p>';
									echo '<input type="hidden" name="editranking" value="1" />';
									echo '<input type="hidden" name="nuser" value="'.(isset($_POST['nuser'])?$_POST['nuser']:$player['nuser']).'" />';
									echo '<input type="hidden" name="ngame" value="'.(isset($_POST['ngame'])?$_POST['ngame']:$game['nurl']).'" />';
									echo '<input type="hidden" name="join" value="1" />';
									echo '<label><span class="field"><input type="submit" value="Join Rankings" /></span></label>';
									echo '</form>';
								}
							}
							echo '</div>';
						}
					} else {
						showErrorMessage('You do not have permission to edit this profile.');
					}
				}
				if ($showprofile) {
					//title
					echo '<div id="title">';
					echo dbUserIsAdmin($account['nuser'])||$account['nuser']==$_GET['p']?'<p class="actions"><a href="'.getUrl('player-edit','%PLAYER%',$_GET['p']).'" class="button">edit</a></p>':null;
					echo strlen($player['avatar'])>0?'<img src="'.$player['avatar'].'" title="'.$player['ndisplay'].'\'s Avatar" class="avatar" />':'';
					echo '<h1>'.$player['ndisplay'].'</h1>';
					echo '</div>';
					echo '<div id="content">';
					//bio+info
					echo '<div id="player-info">';
					echo '<h2>About '.$player['ndisplay'].'</h2>';
					echo strlen($player['bio'])>0?'<p class="bio">'.str_replace("\n",'<br/>',$player['bio']).'</p>':null;
					echo strlen($player['nreal'])>0?'<h3>Real Name</h3><p>'.$player['nreal'].'</p>':'';
					echo strlen($player['location'])>0?'<h3>Location</h3><p>'.$player['location'].'</p>':'';
					echo strlen($player['ttvid'])>0?'<h3>Twitch</h3><p><a href="http://twitch.tv/'.$player['ttvid'].'"><img src="http://puu.sh/8hCAI.png" />'.$player['ttvid'].'</a></p>':'';
					echo strlen($player['ytid'])>0?'<h3>YouTube</h3><p><a href="http://youtube.com/'.$player['ytid'].'"><img src="http://puu.sh/8hCz8.png" />'.$player['ytid'].'</a></p>':'';
					echo strlen($player['twid'])>0?'<h3>Twitter</h3><p><a href="http://twitter.com/'.$player['twid'].'"><img src="http://puu.sh/8hCXC.png" />'.$player['twid'].'</a></p>':'';
					if ($player['birthdate']!='0000-00-00 00:00:00') {
						$birth = new DateTime($player['birthdate']);
						$today = new DateTime();
						$difference = $today->diff($birth);
						echo '<h3>Age</h3><p>'.$difference->format('%y').' years old</p>';
					}
					$joined = new DateTime($player['joined']);
					echo '<h3>Joined</h3><p>'.$joined->format($_SETTINGGLOBAL['date-format-class']).'</p>';
					echo '<h3 style="color:#'.nameToColor($player['nuser']).';">Player Color</h3>';
					echo '</div>';
					//rankings
					echo '<div id="player-rankings">';
					$games = dbGetAllGames();
					$totals = mysql_query('SELECT * FROM totals WHERE userid='.$player['id'].' ORDER BY gameid ASC') or die('<p>'.mysql_error().'</p>');
					echo '<h2>Ranking Statistics</h2>';
					$joincount = 0;
					foreach ($games as $game) {
						if ($total = dbGetTotal($player['nuser'],$game['nurl'])) {
							$prank = dbGetPointRank($player['nuser'],$game['nurl']);
							$trank = dbGetTimeRank($player['nuser'],$game['nurl']);
							echo '<div class="rankstats">';
							echo '<h3>'.$game['nfull'].'</h3>';
							echo '<div class="point"><span class="title">Point Rank</span><span class="rank rnk'.$prank.'">'.nToRank($prank).'</span><span class="pts">'.$total['points'].'<span class="suffix">pts</span></span><span class="clear"></span></div>';
							echo '<div class="time"><span class="title">Time Rank</span><span class="rank rnk'.$trank.'">'.nToRank($trank).'</span><span class="tme">'.sToTime($total['time']).'</span></div>';
							$dom = floor($total['points']/dbGetMaxPoints($game['nurl'])*100);
							echo '<div class="dom"><span class="title">Domination</span><span class="rating d'.floor($dom/10).'">'.$dom.'%</span></div>';
							echo '<div class="ver"><span class="title">Version Preference</span><span class="info">'.(array_key_exists($total['vpref'],$tagList)?$tagList[$total['vpref']]['name']:'No Preference').'</span></div>';
							$joined = new DateTime($total['joined']);
							echo '<div class="join"><span class="title">Joined Rankings</span><span class="info">'.$joined->format($_SETTINGGLOBAL['date-format-class']).'</span></div>';
							echo '</div>';
							$joincount+=1;
						} else {
							if ($account && ($account['admin']||$account['id']==$player['id'])) {
								echo '<div class="rankstats">';
								echo '<h3>'.$game['nfull'].'</h3>';
								echo '<div class="join"><form name="profile" action="'.getUrl('player-edit','%PLAYER%',$player['nuser']).'" method="post">';
								echo '<input type="hidden" name="editranking" value="1" />';
								echo '<input type="hidden" name="nuser" value="'.(isset($_POST['nuser'])?$_POST['nuser']:$player['nuser']).'" />';
								echo '<input type="hidden" name="ngame" value="'.(isset($_POST['ngame'])?$_POST['ngame']:$game['nurl']).'" />';
								echo '<input type="hidden" name="join" value="1" />';
								echo '<input type="submit" value="Join Rankings" />';
								echo '</form></div>';
								echo '</div>';
							}
						}
					}
					if ($joincount==0) {
						echo '<p class="none">'.$player['ndisplay'].' has not joined any rankings yet.</p>';
					}
					echo '</div>';
					echo '<div id="player-history">';
					echo '<h2>Recent Times</h2>';
					$history = mysql_query('SELECT * FROM times WHERE userid='.$player['id'].' ORDER BY played DESC, treal ASC, tgame ASC LIMIT '.$_SETTINGGLOBAL['player-profile-recent']) or die ('<p>'.mysql_error().'</p>');
					if (mysql_num_rows($history)>0) {
						echo '<div class="timelist"><table>';
						echo '<tr class="head"><td class="cat">Category</td><td class="time">Time</td><td class="date">Date</td></tr>';
						//output times
						while ($hTime = mysql_fetch_array($history)) {
							//data
							$ranking = dbGetRankingById($hTime['rankingid']);
							$game = dbGetGameById($ranking['gameid']);
							//set time type
							if ($ranking['timetype']=="tREAL"||$ranking['timetype']=="tRLONLY") {
								$t1 = "treal";
								$t2 = "tgame";
							} else {
								$t1 = "tgame";
								$t2 = "treal";
							}
							//set time string
							$uStrT = $_SETTINGGLOBAL['url'].str_replace('%TIME%',$hTime['id'],$_URI['time']);
							$tStrTime1 = sToTime($hTime[$t1]);
							$tStrTime2 = sToTime($hTime[$t2]);
							$tStr1 = '<a href="'.$uStrT.'">'.$tStrTime1.'</a>';
							if ($ranking['timetype']=='tGMONLY'||$ranking['timetype']=='tRLONLY') {
								$tStr2 = '';
							} else {
								$tStr2 = '<span class="subtime">'.$tStrTime2.'</span>';
							}
							//get best comparable time
							$best = mysql_query('SELECT * FROM times WHERE rankingid='.$hTime['rankingid'].' AND userid='.$hTime['userid'].' AND difficulty="'.$hTime['difficulty'].'" ORDER BY '.$t1.','.$t2.' ASC LIMIT 1') or die('<p>'.mysql_error().'</p>');
							$best = mysql_fetch_array($best);
							//output row
							echo '<tr class="row">';
							echo '<td class="cat"><a href="'.getUrl('ranking',array('%GAME%','%RANKING%'),array($game['nurl'],$ranking['nurl'])).'">'.$ranking['nfull'].'</a> &mdash; <span class="'.$hTime['difficulty'].'">'.$tagList[$hTime['difficulty']]['name'].'</span></td>';
							echo '<td class="time">'.$tStr1.$tStr2.'</td>';
							echo '<td class="date">'.strftime($_SETTINGGLOBAL['date-format'],strtotime($hTime['played'])).'</td>';
							echo '</tr>';
						}
						echo '</table></div>';
					} else {
						echo '<p class="none">'.$player['ndisplay'].' has not submitted any times yet.</p>';
					}
					echo '</div>';
					echo '</div>';
				}
			}
		} else {
			showErrorMessage('Invalid player specified. <a href="'.$_SETTINGGLOBAL['url'].$_URI['player-list'].'">Please select a proper player</a>.');
		}
	} else {
		//PLAYER LIST
		echo '<div id="title"><h1>Players</h1></div>';
		echo '<div id="content">';
		echo '<p>All registered players.</p>';
		echo '<div id="player-listname">';
		echo '<h2>Players by Name</h2>';
		echo '<div class="timelist"><table>';
		echo '<tr class="head"><td class="player">Username</td><td class="date">Join Date</td></tr>';
		$players = mysql_query('SELECT * FROM users ORDER BY ndisplay,nuser ASC');
		while ($player = mysql_fetch_array($players)) {
			echo '<tr class="row">';
			$url = $_SETTINGGLOBAL['url'].str_replace('%PLAYER%',$player['nuser'],$_URI['player']);
			echo '<td class="player"><a href="'.$url.'">'.$player['ndisplay'].'</a></td>';
			$joined = new DateTime($player['joined']);
			echo '<td class="date">'.$joined->format($_SETTINGGLOBAL['date-format-class']).'</td>';
			echo '</tr>';
		}
		echo '</table></div>';
		echo '</div>';
		echo '<div id="player-listdate">';
		echo '<h2>Players by Join Date</h2>';
		echo '<div class="timelist"><table>';
		echo '<tr class="head"><td class="player">Username</td><td class="date">Join Date</td></tr>';
		$players = mysql_query('SELECT * FROM users ORDER BY joined,nuser ASC');
		while ($player = mysql_fetch_array($players)) {
			echo '<tr class="row">';
			$url = $_SETTINGGLOBAL['url'].str_replace('%PLAYER%',$player['nuser'],$_URI['player']);
			echo '<td class="player"><a href="'.$url.'">'.$player['ndisplay'].'</a></td>';
			$joined = new DateTime($player['joined']);
			echo '<td class="date">'.$joined->format($_SETTINGGLOBAL['date-format-class']).'</td>';
			echo '</tr>';
		}
		echo '</table></div>';
		echo '</div>';
		echo '</div>';
	}
	echo '</div>';
	
	include("embed-footer.php");
?>
		</div>
	</body>
</html>