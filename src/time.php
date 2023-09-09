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
	include("embed-menuplayer.php");



	
	
	$tagList = dbGetTags();
	$edited = false;
	echo '<div id="page">';
	//start
	if (!isset($_GET['t'])) {
		if (isset($_GET['a']) && $_GET['a']=='submit' && isset($_GET['r']) && $ranking = dbGetRankingById(intval($_GET['r']))) {
			$game = dbGetGameById($ranking['gameid']);
			$total = dbGetTotal($account['nuser'],$game['nurl']);
			if ($account && ($account['admin']||$total)) {
				echo '<div id="title"><h1>Submit Time</h1></div>';
				echo '<div id="content">';
				echo '<h2>'.$game['nshort'].' &mdash; '.$ranking['nfull'].'</h2>';
				if ($ranking['timetype']=="tREAL"||$ranking['timetype']=="tRLONLY") {
					$t1 = "treal";
					$t2 = "tgame";
				} else {
					$t1 = "tgame";
					$t2 = "treal";
				}
				$showform = true;
				if (isset($_POST['submittime']) && intval($_POST['submittime'])) {
					$showform = false;
					//error handling
					if (!isset($_POST['rankingid']) || $_POST['rankingid']!=$_GET['r']) {
						$showform = true;
						showErrorMessage('Ranking invalid.');
					}
					if (isset($_POST['userid']) && intval($_POST['userid'])) {
						$u = dbGetUserById($_POST['userid']);
						if (!dbGetTotal($account['nuser'],$game['nurl'])) {
							$showform = true;
							showErrorMessage('Rankings for this time have not been joined.');
						}
						if (!(dbUserCanEditTimes($account['nuser'],$game['nurl'])||$account['id']==$_POST['userid'])) {
							$showform = true;
							showErrorMessage('You do not have the required account permissions.');
						}
					} else {
						$showform = true;
						showErrorMessage('User ID invalid.');
					}
					if (!(isset($_POST[$t1.'H'])&&isset($_POST[$t1.'M'])&&isset($_POST[$t1.'S'])) || timeToS(intval($_POST[$t1.'H']).':'.intval($_POST[$t1.'M']).':'.intval($_POST[$t1.'S']))<1) {
						$showform = true;
						showErrorMessage(($t1=='treal'?'Real Time':'Game Time').' invalid.');
					}
					if ($ranking['timetype']!='tRLONLY' && $ranking['timetype']!='tGMONLY' && (!(isset($_POST[$t2.'H'])&&isset($_POST[$t2.'M'])&&isset($_POST[$t2.'S'])) || timeToS(intval($_POST[$t2.'H']).':'.intval($_POST[$t2.'M']).':'.intval($_POST[$t2.'S']))<1)) {
						$showform = true;
						showErrorMessage(($t2=='treal'?'Real Time':'Game Time').' invalid.');
					}
					if (!isset($_POST['difficulty']) || !preg_match('/'.str_replace('%TAG%',$_POST['difficulty'],$_VALIDATION['tagexists']).'/',$game['difficulties'])) {
						$showform = true;
						showErrorMessage('Difficulty invalid.');
					}
					if (!isset($_POST['version']) || !preg_match('/'.str_replace('%TAG%',$_POST['version'],$_VALIDATION['tagexists']).'/',$game['versions'])) {
						$showform = true;
						showErrorMessage('Version invalid.');
					}
					if (!$showform) {
						$wr = 0;
						$ranks = array();
						$r = mysql_query('SELECT id,piggyback FROM rankings WHERE id='.$ranking['id'].' OR piggyback='.$ranking['id']);
						while ($rank = mysql_fetch_array($r)) {
							$qDif = $_POST['difficulty']!="dALL"?" AND difficulty='".$_POST['difficulty']."'":"";
							$qRankExt = $rank['piggyback']>0?' OR rankingid='.$rank['piggyback']:'';
							$query = 'SELECT * FROM (SELECT *,(@rnk:=@rnk+1)rnk,(@rank:=IF(@ct1=treal AND @ct2=tgame,@rank,@rnk))rank,(@ct1:=treal)nt1,(@ct2:=tgame)nt2 FROM (SELECT times.* FROM times JOIN (SELECT userid,MIN('.$t1.')'.$t1.' FROM times WHERE (rankingid='.$ranking['id'].$qRankExt.')'.$qDif.' GROUP BY userid)fastest ON (fastest.userid = times.userid AND fastest.'.$t1.' = times.'.$t1.') WHERE (rankingid='.$ranking['id'].$qRankExt.')'.$qDif.' ORDER BY '.$t1.','.$t2.',played,id ASC)times)times WHERE userid='.$u['id'].' OR rank=1 ORDER BY rank,played,id ASC';
							foreach (explode(";","SET @rnk=0;SET @rank=0;SET @ct1=0;SET @ct2=0") as $subquery) { mysql_query($subquery); }
							$times = mysql_query($query);
							$ranks[] = array('id'=>$rank['id'],'rank'=>0);
							while ($time = mysql_fetch_array($times)) {
								$wr = $time[$t1]>$wr&&$time['rank']==1?$time[$t1]:$wr;
								if ($time['userid']==$_POST['userid']) {
									$ranks[count($ranks)-1]['rank'] = $time['rank'];
								}
							}
						}
					}
					if (!isset($_POST['video']) || (strlen($_POST['video'])>0 && !filter_var($_POST['video'],FILTER_VALIDATE_URL))) {
						$showform = true;
						showErrorMessage('Video invalid.');
					}
					if (isset($wr) && strlen($_POST['video'])==0 && timeToS(intval($_POST[$t1.'H']).':'.intval($_POST[$t1.'M']).':'.intval($_POST[$t1.'S']))<=$wr && !dbUserCanEditTimes($account['nuser'],$game['nurl'])) {
						$showform = true;
						showErrorMessage('Video required for world record times.');
					}
					$now = new DateTime();
					if (!isset($_POST['achieveYe']) || intval($_POST['achieveYe'])>$now->format('Y') || !isset($_POST['achieveMo']) || intval($_POST['achieveMo'])>12 || !isset($_POST['achieveDa']) || intval($_POST['achieveDa'])>31 || !isset($_POST['achieveHo']) || intval($_POST['achieveHo'])>23 || !isset($_POST['achieveMi']) || intval($_POST['achieveMi'])>59 || !isset($_POST['achieveSe']) || intval($_POST['achieveSe'])>59) {
						$showform = true;
						showErrorMessage('Achievement date invalid.');
					}
					if (!$showform) {
						$played = new DateTime(intval($_POST['achieveYe']).'-'.intval($_POST['achieveMo']).'-'.intval($_POST['achieveDa']).' '.intval($_POST['achieveHo']).':'.intval($_POST['achieveMi']).':'.intval($_POST['achieveSe']));
						$t2text = $ranking['timetype']!='tRLONLY'&&$ranking['timetype']!='tGMONLY'?intval(timeToS(intval($_POST[$t2.'H']).':'.intval($_POST[$t2.'M']).':'.intval($_POST[$t2.'S']))):0;
						if (mysql_query('INSERT INTO times (rankingid,userid,'.$t1.','.$t2.',played,video,comment,version,difficulty) VALUES ('.$_GET['r'].','.$u['id'].','.intval(timeToS(intval($_POST[$t1.'H']).':'.intval($_POST[$t1.'M']).':'.intval($_POST[$t1.'S']))).','.$t2text.',"'.$played->format('Y-m-d H:i:s').'","'.mysql_real_escape_string(strip_tags($_POST['video'])).'","'.mysql_real_escape_string(strip_tags($_POST['comment'])).'","'.$_POST['version'].'","'.$_POST['difficulty'].'")')) {
							showSuccessMessage('Your hate has made you powerful.');
							$calculated = array();
							foreach ($ranks as $rank) {
								$ranking = dbGetRankingById($rank['id']);
								//pb ref
								$qDif = $_POST['difficulty']!="dALL"?" AND difficulty='".$_POST['difficulty']."'":"";
								$qRankExt = $ranking['piggyback']>0?' OR rankingid='.$ranking['piggyback']:'';
								$query = 'SELECT * FROM (SELECT *,(@rnk:=@rnk+1)rnk,(@rank:=IF(@ct1=treal AND @ct2=tgame,@rank,@rnk))rank,(@ct1:=treal)nt1,(@ct2:=tgame)nt2 FROM (SELECT times.* FROM times JOIN (SELECT userid,MIN('.$t1.')'.$t1.' FROM times WHERE (rankingid='.$ranking['id'].$qRankExt.')'.$qDif.' GROUP BY userid)fastest ON (fastest.userid = times.userid AND fastest.'.$t1.' = times.'.$t1.') WHERE (rankingid='.$ranking['id'].$qRankExt.')'.$qDif.' ORDER BY '.$t1.','.$t2.',played,id ASC)times)times WHERE userid='.$u['id'].' ORDER BY rank,played,id ASC LIMIT 1';
								foreach (explode(";","SET @rnk=0;SET @rank=0;SET @ct1=0;SET @ct2=0") as $subquery) { mysql_query($subquery); }
								$pb = mysql_fetch_array(mysql_query($query));
								//other times
								$qDif = $_POST['difficulty']!="dALL"?" AND difficulty='".$_POST['difficulty']."'":"";
								$qRankExt = $ranking['piggyback']>0?' OR rankingid='.$ranking['piggyback']:'';
								$query = 'SELECT * FROM (SELECT *,(@rnk:=@rnk+1)rnk,(@rank:=IF(@ct1=treal AND @ct2=tgame,@rank,@rnk))rank,(@ct1:=treal)nt1,(@ct2:=tgame)nt2 FROM (SELECT times.* FROM times JOIN (SELECT userid,MIN('.$t1.')'.$t1.' FROM times WHERE (rankingid='.$ranking['id'].$qRankExt.')'.$qDif.' GROUP BY userid)fastest ON (fastest.userid = times.userid AND fastest.'.$t1.' = times.'.$t1.') WHERE (rankingid='.$ranking['id'].$qRankExt.')'.$qDif.' ORDER BY '.$t1.','.$t2.',played,id ASC)times)times WHERE rank>='.min($pb['rank'],$rank['rank']).($rank['rank']>0?' AND rank<='.max($pb['rank'],$rank['rank']):null).' ORDER BY rank,played,id ASC';
								foreach (explode(";","SET @rnk=0;SET @rank=0;SET @ct1=0;SET @ct2=0") as $subquery) { mysql_query($subquery); }
								$times = mysql_query($query);
								while ($time = mysql_fetch_array($times)) {
									if (!in_array($time['userid'],$calculated)) {
										$user = dbGetUserById($time['userid']);
										if (!dbUpdateTotal($user['nuser'],$game['nurl'])) {
											showErrorMessage('Failed to update totals for '.$user['ndisplay'].'.');
										}
										$calculated[] = $time['userid'];
									}
								}
							}
							if ($final = mysql_fetch_array(mysql_query('SELECT id FROM times WHERE userid='.$u['id'].' AND rankingid='.$_GET['r'].' ORDER BY id DESC LIMIT 1'))) {
								$_GET['t'] = $final['id'];
							}
						} else {
							$showform = true;
							showErrorMessage('Submission failed. Please try again.');
						}
					}
				}
				if ($showform) {
					echo '<form name="submit" action="'.getUrl('time-submit-rank','%RANKINGNO%',intval($_GET['r'])).'" method="post">';
					echo '<input type="hidden" name="submittime" value="1" />';
					echo '<input type="hidden" name="rankingid" value="'.(isset($_POST['rankingid'])?$_POST['rankingid']:$ranking['id']).'" />';
					echo '<div class="label"><span class="name">'.($t1=='treal'?'Real Time':'Game Time').'</span><span class="field"><input type="text" name="'.$t1.'H" value="'.(isset($_POST[$t1.'H'])?$_POST[$t1.'H']:0).'" />:<input type="text" name="'.$t1.'M" value="'.(isset($_POST[$t1.'M'])?$_POST[$t1.'M']:0).'" />:<input type="text" name="'.$t1.'S" value="'.(isset($_POST[$t1.'S'])?$_POST[$t1.'S']:0).'" /></span></div>';
					if ($ranking['timetype']!='tRLONLY'&&$ranking['timetype']!='tGMONLY') {
						echo '<div class="label"><span class="name">'.($t2=='treal'?'Real Time':'Game Time').'</span><span class="field"><input type="text" name="'.$t2.'H" value="'.(isset($_POST[$t2.'H'])?$_POST[$t2.'H']:0).'" />:<input type="text" name="'.$t2.'M" value="'.(isset($_POST[$t2.'M'])?$_POST[$t2.'M']:0).'" />:<input type="text" name="'.$t2.'S" value="'.(isset($_POST[$t2.'S'])?$_POST[$t2.'S']:0).'" /></span></div>';
					}
					echo '<label><span class="name">Difficulty</span><span class="field"><span class="select-wrapper"><select name="difficulty">';
					$select = isset($_POST['difficulty'])?$_POST['difficulty']:'';
					foreach (explode(" ",$game['difficulties']) as $difficulty) {
						echo $difficulty=='dALL'?null:'<option value="'.$difficulty.'"'.($select==$difficulty?' selected="selected"':null).' class="'.$difficulty.'">'.$tagList[$difficulty]['name'].'</option>';
					}
					echo '</select></span></span></label>';
					echo '<label><span class="name">Version</span><span class="field"><span class="select-wrapper"><select name="version">';
					$select = isset($_POST['version'])?$_POST['version']:$total['vpref'];
					foreach (explode(" ",$game['versions']) as $version) {
						echo '<option value="'.$version.'"'.($select==$version?' selected="selected"':null).'>'.$tagList[$version]['name'].'</option>';
					}
					echo '</select></span></span></label>';
					echo '<label><span class="name">Video</span><span class="field"><input type="text" name="video"'.(isset($_POST['video'])?' value="'.$_POST['video'].'"':'').' /></span></label>';
					echo '<label><span class="name">Comment</span><span class="field"><textarea name="comment">'.(isset($_POST['comment'])?$_POST['comment']:null).'</textarea></span></label>';
					$now = new DateTime();
					echo '<div class="label"><span class="name">Achieved</span><span class="field"><input type="text" name="achieveYe" value="'.(isset($_POST['achieveYe'])?intval($_POST['achieveYe']):$now->format('Y')).'" />/<input type="text" name="achieveMo" value="'.(isset($_POST['achieveMo'])?intval($_POST['achieveMo']):$now->format('m')).'" />/<input type="text" name="achieveDa" value="'.(isset($_POST['achieveDa'])?intval($_POST['achieveDa']):$now->format('d')).'" />&nbsp;&nbsp;&nbsp;<input type="text" name="achieveHo" value="'.(isset($_POST['achieveHo'])?intval($_POST['achieveHo']):$now->format('H')).'" />:<input type="text" name="achieveMi" value="'.(isset($_POST['achieveMi'])?intval($_POST['achieveMi']):$now->format('i')).'" />:<input type="text" name="achieveSe" value="'.(isset($_POST['achieveSe'])?intval($_POST['achieveSe']):$now->format('s')).'" /></span></div>';
					if (dbUserCanEditTimes($account['nuser'],$game['nurl'])) {
						$users = dbGetAllUsers();
						echo '<label><span class="name">User</span><span class="field"><span class="select-wrapper"><select name="userid">';
						$select = isset($_POST['userid'])?$_POST['userid']:$account['id'];
						foreach ($users as $user) {
							echo '<option value="'.$user['id'].'"'.($user['id']==$select?' selected="selected"':null).'>'.$user['ndisplay'].'</option>';
						}
						echo '</select></span></span></label>';
					} else {
						echo '<input type="hidden" name="userid" value="'.(isset($_POST['userid'])?$_POST['userid']:$account['id']).'" />';
					}
					echo '<div class="label"><span class="field"><input type="submit" value="Submit Time" /></span></div>';
					echo '</form>';
				}
				echo '</div>';
			} else {
				echo '<div id="content">';
				showErrorMessage('You need to be signed in and have joined the game\'s rankings to submit a time.');
				echo '</div>';
			}
		} else {
			echo '<div id="content">';
			showErrorMessage('No time specified. <a href="'.getUrl().'">Please choose one</a>');
			echo '</div>';
		}
	}
	if (isset($_GET['t'])) {
		$time = dbGetTime($_GET['t']);
		if ($time) {
			$showtime = true;
			//get data
			$player = dbGetUserById($time['userid']);
			$ranking = dbGetRankingById($time['rankingid']);
			$game = dbGetGameById($ranking['gameid']);
			//calc points
			$points = explode(",",$game['pointscale']);
			for ($a=0;$a<count($points);$a++) {
				$points[$a] = floor($ranking['maxpoints']*$points[$a]/100);
			}
			//time formatting
			if ($ranking['timetype']=="tREAL"||$ranking['timetype']=="tRLONLY") {
				$t1 = "treal";
				$t2 = "tgame";
			} else {
				$t1 = "tgame";
				$t2 = "treal";
			}
			$tStrTime1 = sToTime($time[$t1]);
			$tStrTime2 = sToTime($time[$t2]);
			$tStr1 = $tStrTime1;
			if ($ranking['timetype']=='tGMONLY'||$ranking['timetype']=='tRLONLY') {
				$tStr2 = '';
			} else {
				$tStr2 = '<span class="subtime">/'.$tStrTime2.'</span>';
			}
			//TIME EDITING
			if (isset($_GET['a']) && $_GET['a']=='edit' && (dbUserCanEditTimes($account['nuser'],$game['nurl'])||$account['id']==$player['id'])) {
				$showtime = false;
				echo '<div id="title"><h1>Edit Time &mdash; '.$tStr1.$tStr2.' by '.$player['ndisplay'].'</h1><h2><a href="'.getUrl('ranking',array('%GAME%','%RANKING%'),array($game['nurl'],$ranking['nurl'])).'">'.$ranking['nfull'].'</a> &mdash; <span class="'.$time['difficulty'].'">'.$tagList[$time['difficulty']]['name'].'</span></h2></div>';
				echo '<div id="content">';
				$showform = true;
				if (isset($_POST['edittime']) && intval($_POST['edittime'])) {
					$showform = false;
					//error handling
					if (!isset($_POST['timeid']) || $_POST['timeid']!=$time['id']) {
						$showform = true;
						showErrorMessage('Time ID invalid.');
					}
					if (!(isset($_POST[$t1.'H'])&&isset($_POST[$t1.'M'])&&isset($_POST[$t1.'S'])) || timeToS(intval($_POST[$t1.'H']).':'.intval($_POST[$t1.'M']).':'.intval($_POST[$t1.'S']))<1 || intval($_POST[$t1.'M'])>59 || intval($_POST[$t1.'S'])>59) {
						$showform = true;
						showErrorMessage(($t1=='treal'?'Real Time':'Game Time').' invalid.');
					}
					if ($ranking['timetype']!='tRLONLY' && $ranking['timetype']!='tGMONLY' && (!(isset($_POST[$t2.'H'])&&isset($_POST[$t2.'M'])&&isset($_POST[$t2.'S'])) || timeToS(intval($_POST[$t2.'H']).':'.intval($_POST[$t2.'M']).':'.intval($_POST[$t2.'S']))<1 || intval($_POST[$t2.'M'])>59 || intval($_POST[$t2.'S'])>59)) {
						$showform = true;
						showErrorMessage(($t2=='treal'?'Real Time':'Game Time').' invalid.');
					}
					if (!isset($_POST['difficulty']) || !preg_match('/'.str_replace('%TAG%',$_POST['difficulty'],$_VALIDATION['tagexists']).'/',$game['difficulties'])) {
						$showform = true;
						showErrorMessage('Difficulty invalid.');
					}
					if (!isset($_POST['version']) || !preg_match('/'.str_replace('%TAG%',$_POST['version'],$_VALIDATION['tagexists']).'/',$game['versions'])) {
						$showform = true;
						showErrorMessage('Version invalid.');
					}
					if (!$showform) {
						$wr = 0;
						$ranks = array();
						$r = mysql_query('SELECT id,piggyback FROM rankings WHERE id='.$ranking['id'].' OR piggyback='.$ranking['id']);
						while ($rank = mysql_fetch_array($r)) {
							$qDif = $_POST['difficulty']!="dALL"?" AND difficulty='".$_POST['difficulty']."'":"";
							$qRankExt = $rank['piggyback']>0?' OR rankingid='.$rank['piggyback']:'';
							$query = 'SELECT * FROM (SELECT *,(@rnk:=@rnk+1)rnk,(@rank:=IF(@ct1=treal AND @ct2=tgame,@rank,@rnk))rank,(@ct1:=treal)nt1,(@ct2:=tgame)nt2 FROM (SELECT times.* FROM times JOIN (SELECT userid,MIN('.$t1.')'.$t1.' FROM times WHERE (rankingid='.$ranking['id'].$qRankExt.')'.$qDif.' GROUP BY userid)fastest ON (fastest.userid = times.userid AND fastest.'.$t1.' = times.'.$t1.') WHERE (rankingid='.$ranking['id'].$qRankExt.')'.$qDif.' ORDER BY '.$t1.','.$t2.',played,id ASC)times)times WHERE userid='.$player['id'].' OR rank=1 ORDER BY rank,played,id ASC';
							foreach (explode(";","SET @rnk=0;SET @rank=0;SET @ct1=0;SET @ct2=0") as $subquery) { mysql_query($subquery); }
							$times = mysql_query($query);
							$ranks[] = array('id'=>$rank['id'],'rank'=>0);
							while ($t = mysql_fetch_array($times)) {
								$wr = $t[$t1]>$wr&&$t['rank']==1?$t[$t1]:$wr;
								if ($t['userid']==$time['userid']) {
									$ranks[count($ranks)-1]['rank'] = $t['rank'];
								}
							}
						}
					}
					if (!isset($_POST['video']) || (strlen($_POST['video'])>0 && !filter_var($_POST['video'],FILTER_VALIDATE_URL))) {
						$showform = true;
						showErrorMessage('Video invalid.');
					}
					if (isset($wr) && strlen($_POST['video'])==0 && timeToS(intval($_POST[$t1.'H']).':'.intval($_POST[$t1.'M']).':'.intval($_POST[$t1.'S']))<=$wr && !dbUserCanEditTimes($account['nuser'],$game['nurl'])) {
						$showform = true;
						showErrorMessage('Video required for world record times.');
					}
					if (!isset($_POST['achieveYe']) || !isset($_POST['achieveMo']) || !isset($_POST['achieveDa']) || !isset($_POST['achieveHo']) || !isset($_POST['achieveMi']) || !isset($_POST['achieveSe'])) {
						$showform = true;
						showErrorMessage('Achievement date not provided.');
					}
					if (!$showform) {
						$played = new DateTime(intval($_POST['achieveYe']).'-'.intval($_POST['achieveMo']).'-'.intval($_POST['achieveDa']).' '.intval($_POST['achieveHo']).':'.intval($_POST['achieveMi']).':'.intval($_POST['achieveSe']));
						$t2text = $ranking['timetype']!='tRLONLY'&&$ranking['timetype']!='tGMONLY'?intval(timeToS(intval($_POST[$t2.'H']).':'.intval($_POST[$t2.'M']).':'.intval($_POST[$t2.'S']))):0;
						if (mysql_query('UPDATE times SET '.$t1.'='.intval(timeToS(intval($_POST[$t1.'H']).':'.intval($_POST[$t1.'M']).':'.intval($_POST[$t1.'S']))).','.$t2.'='.$t2text.',played="'.$played->format('Y-m-d H:i:s').'",video="'.mysql_real_escape_string(strip_tags($_POST['video'])).'",comment="'.mysql_real_escape_string(strip_tags($_POST['comment'])).'",version="'.$_POST['version'].'",difficulty="'.$_POST['difficulty'].'" WHERE id='.$time['id'])) {
							showSuccessMessage('Time edited.');
							$calculated = array();
							foreach ($ranks as $rank) {
								$ranking = dbGetRankingById($rank['id']);
								//pb ref
								$qDif = $_POST['difficulty']!="dALL"?" AND difficulty='".$_POST['difficulty']."'":"";
								$qRankExt = $ranking['piggyback']>0?' OR rankingid='.$ranking['piggyback']:'';
								$query = 'SELECT * FROM (SELECT *,(@rnk:=@rnk+1)rnk,(@rank:=IF(@ct1=treal AND @ct2=tgame,@rank,@rnk))rank,(@ct1:=treal)nt1,(@ct2:=tgame)nt2 FROM (SELECT times.* FROM times JOIN (SELECT userid,MIN('.$t1.')'.$t1.' FROM times WHERE (rankingid='.$ranking['id'].$qRankExt.')'.$qDif.' GROUP BY userid)fastest ON (fastest.userid = times.userid AND fastest.'.$t1.' = times.'.$t1.') WHERE (rankingid='.$ranking['id'].$qRankExt.')'.$qDif.' ORDER BY '.$t1.','.$t2.',played,id ASC)times)times WHERE userid='.$time['userid'].' ORDER BY rank,played,id ASC LIMIT 1';
								foreach (explode(";","SET @rnk=0;SET @rank=0;SET @ct1=0;SET @ct2=0") as $subquery) { mysql_query($subquery); }
								$pb = mysql_fetch_array(mysql_query($query));
								//other times
								$qDif = $_POST['difficulty']!="dALL"?" AND difficulty='".$_POST['difficulty']."'":"";
								$qRankExt = $ranking['piggyback']>0?' OR rankingid='.$ranking['piggyback']:'';
								$query = 'SELECT * FROM (SELECT *,(@rnk:=@rnk+1)rnk,(@rank:=IF(@ct1=treal AND @ct2=tgame,@rank,@rnk))rank,(@ct1:=treal)nt1,(@ct2:=tgame)nt2 FROM (SELECT times.* FROM times JOIN (SELECT userid,MIN('.$t1.')'.$t1.' FROM times WHERE (rankingid='.$ranking['id'].$qRankExt.')'.$qDif.' GROUP BY userid)fastest ON (fastest.userid = times.userid AND fastest.'.$t1.' = times.'.$t1.') WHERE (rankingid='.$ranking['id'].$qRankExt.')'.$qDif.' ORDER BY '.$t1.','.$t2.',played,id ASC)times)times WHERE rank>='.min($pb['rank'],$rank['rank']).($rank['rank']>0?' AND rank<='.max($pb['rank'],$rank['rank']):null).' ORDER BY rank,played,id ASC';
								foreach (explode(";","SET @rnk=0;SET @rank=0;SET @ct1=0;SET @ct2=0") as $subquery) { mysql_query($subquery); }
								$times = mysql_query($query);
								while ($time = mysql_fetch_array($times)) {
									if (!in_array($time['userid'],$calculated)) {
										$user = dbGetUserById($time['userid']);
										if (!dbUpdateTotal($user['nuser'],$game['nurl'])) {
											showErrorMessage('Failed to update totals for '.$user['ndisplay'].'.');
										}
										$calculated[] = $time['userid'];
									}
								}
							}
							$time = dbGetTime($_GET['t']);
							$showtime = true;
							$edited = true;
						} else {
							$showform = true;
							showErrorMessage('Edit failed. Please try again.');
						}
					}
				}
				if ($showform) {
					echo '<form name="edit" action="'.getUrl('time-edit','%TIME%',$_GET['t']).'" method="post">';
					echo '<input type="hidden" name="edittime" value="1" />';
					echo '<input type="hidden" name="timeid" value="'.(isset($_POST['timeid'])?$_POST['timeid']:$time['id']).'" />';
					preg_match($_VALIDATION['time'],sToTime($time[$t1]),$m);
					echo '<div class="label"><span class="name">'.($t1=='treal'?'Real Time':'Game Time').'</span><span class="field"><input type="text" name="'.$t1.'H" value="'.(isset($_POST[$t1.'H'])?$_POST[$t1.'H']:$m['h']).'" />:<input type="text" name="'.$t1.'M" value="'.(isset($_POST[$t1.'M'])?$_POST[$t1.'M']:$m['m']).'" />:<input type="text" name="'.$t1.'S" value="'.(isset($_POST[$t1.'S'])?$_POST[$t1.'S']:$m['s']).'" /></span></div>';
					if ($ranking['timetype']!='tRLONLY'&&$ranking['timetype']!='tGMONLY') {
						preg_match($_VALIDATION['time'],sToTime($time[$t2]),$m);
						echo '<div class="label"><span class="name">'.($t2=='treal'?'Real Time':'Game Time').'</span><span class="field"><input type="text" name="'.$t2.'H" value="'.(isset($_POST[$t2.'H'])?$_POST[$t2.'H']:$m['h']).'" />:<input type="text" name="'.$t2.'M" value="'.(isset($_POST[$t2.'M'])?$_POST[$t2.'M']:$m['m']).'" />:<input type="text" name="'.$t2.'S" value="'.(isset($_POST[$t2.'S'])?$_POST[$t2.'S']:$m['s']).'" /></span></div>';
					}
					echo '<label><span class="name">Difficulty</span><span class="field"><span class="select-wrapper"><select name="difficulty">';
					$select = isset($_POST['difficulty'])?$_POST['difficulty']:$time['difficulty'];
					foreach (explode(" ",$game['difficulties']) as $difficulty) {
						echo $difficulty=='dALL'?null:'<option value="'.$difficulty.'"'.($select==$difficulty?' selected="selected"':null).' class="'.$difficulty.'">'.$tagList[$difficulty]['name'].'</option>';
					}
					echo '</select></span></span></label>';
					echo '<label><span class="name">Version</span><span class="field"><span class="select-wrapper"><select name="version">';
					$select = isset($_POST['version'])?$_POST['version']:$time['version'];
					foreach (explode(" ",$game['versions']) as $version) {
						echo '<option value="'.$version.'"'.($select==$version?' selected="selected"':null).'>'.$tagList[$version]['name'].'</option>';
					}
					echo '</select></span></span></label>';
					echo '<label><span class="name">Video</span><span class="field"><input type="text" name="video" value="'.(isset($_POST['video'])?$_POST['video']:$time['video']).'" /></span></label>';
					echo '<label><span class="name">Comment</span><span class="field"><textarea name="comment">'.(isset($_POST['comment'])?$_POST['comment']:$time['comment']).'</textarea></span></label>';
					$now = new DateTime($time['played']);
					echo '<div class="label"><span class="name">Achieved</span><span class="field"><input type="text" name="achieveYe" value="'.(isset($_POST['achieveYe'])?$_POST['achieveYe']:$now->format('Y')).'" />/<input type="text" name="achieveMo" value="'.(isset($_POST['achieveMo'])?$_POST['achieveMo']:$now->format('m')).'" />/<input type="text" name="achieveDa" value="'.(isset($_POST['achieveDa'])?$_POST['achieveDa']:$now->format('d')).'" />&nbsp;&nbsp;&nbsp;<input type="text" name="achieveHo" value="'.(isset($_POST['achieveHo'])?$_POST['achieveHo']:$now->format('H')).'" />:<input type="text" name="achieveMi" value="'.(isset($_POST['achieveMi'])?$_POST['achieveMi']:$now->format('i')).'" />:<input type="text" name="achieveSe" value="'.(isset($_POST['achieveSe'])?$_POST['achieveSe']:$now->format('s')).'" /></span></div>';
					echo '<label><span class="field"><input type="submit" value="Edit Time" /></span></label>';
					echo '</form>';
				}
			}
			//TIME DELETION
			if (isset($_GET['a']) && $_GET['a']=='delete' && (dbUserCanEditTimes($account['nuser'],$game['nurl'])||$account['id']==$player['id'])) {
				echo '<h1>Delete Time</h1>';
				echo '<h2>'.$player['ndisplay'].' &mdash; '.$ranking['nfull'].' ('.$tagList[$time['difficulty']]['name'].') &mdash; '.$tStr1.$tStr2.'</h2>';
				$showform = true;
				if (isset($_POST['deleteconfirm']) && intval($_POST['deleteconfirm'])) {
					$showform = false;
					$ranks = array();
					$r = mysql_query('SELECT id,piggyback FROM rankings WHERE id='.$ranking['id'].' OR piggyback='.$ranking['id']);
					while ($rank = mysql_fetch_array($r)) {
						$qDif = $time['difficulty']!="dALL"?" AND difficulty='".$time['difficulty']."'":"";
						$qRankExt = $ranking['piggyback']>0?' OR rankingid='.$ranking['piggyback']:'';
						$query = 'SELECT * FROM (SELECT *,(@rnk:=@rnk+1)rnk,(@rank:=IF(@ct1=treal AND @ct2=tgame,@rank,@rnk))rank,(@ct1:=treal)nt1,(@ct2:=tgame)nt2 FROM (SELECT times.* FROM times JOIN (SELECT userid,MIN('.$t1.')'.$t1.' FROM times WHERE (rankingid='.$ranking['id'].$qRankExt.')'.$qDif.' GROUP BY userid)fastest ON (fastest.userid = times.userid AND fastest.'.$t1.' = times.'.$t1.') WHERE (rankingid='.$ranking['id'].$qRankExt.')'.$qDif.' ORDER BY '.$t1.','.$t2.',played,id ASC)times)times WHERE userid='.$player['id'];
						foreach (explode(";","SET @rnk=0;SET @rank=0;SET @ct1=0;SET @ct2=0") as $subquery) { mysql_query($subquery); }
						if ($t = mysql_fetch_array(mysql_query($query))) {
							$ranks[] = array('id'=>$rank['id'],'rank'=>$t['rank']);
						}
					}
					if (mysql_query('DELETE FROM times WHERE id='.$time['id'])) {
						showSuccessMessage('Time deleted.');
						$calculated = array();
						foreach ($ranks as $rank) {
							$ranking = dbGetRankingById($rank['id']);
							//pb ref
							$qDif = $time['difficulty']!="dALL"?" AND difficulty='".$time['difficulty']."'":"";
							$qRankExt = $ranking['piggyback']>0?' OR rankingid='.$ranking['piggyback']:'';
							$query = 'SELECT * FROM (SELECT *,(@rnk:=@rnk+1)rnk,(@rank:=IF(@ct1=treal AND @ct2=tgame,@rank,@rnk))rank,(@ct1:=treal)nt1,(@ct2:=tgame)nt2 FROM (SELECT times.* FROM times JOIN (SELECT userid,MIN('.$t1.')'.$t1.' FROM times WHERE (rankingid='.$ranking['id'].$qRankExt.')'.$qDif.' GROUP BY userid)fastest ON (fastest.userid = times.userid AND fastest.'.$t1.' = times.'.$t1.') WHERE (rankingid='.$ranking['id'].$qRankExt.')'.$qDif.' ORDER BY '.$t1.','.$t2.',played,id ASC)times)times WHERE userid='.$player['id'].' ORDER BY rank,played,id ASC LIMIT 1';
							foreach (explode(";","SET @rnk=0;SET @rank=0;SET @ct1=0;SET @ct2=0") as $subquery) { mysql_query($subquery); }
							$pb = mysql_fetch_array(mysql_query($query));
							//other times
							$qDif = $time['difficulty']!="dALL"?" AND difficulty='".$time['difficulty']."'":"";
							$qRankExt = $ranking['piggyback']>0?' OR rankingid='.$ranking['piggyback']:'';
							$query = 'SELECT * FROM (SELECT *,(@rnk:=@rnk+1)rnk,(@rank:=IF(@ct1=treal AND @ct2=tgame,@rank,@rnk))rank,(@ct1:=treal)nt1,(@ct2:=tgame)nt2 FROM (SELECT times.* FROM times JOIN (SELECT userid,MIN('.$t1.')'.$t1.' FROM times WHERE (rankingid='.$ranking['id'].$qRankExt.')'.$qDif.' GROUP BY userid)fastest ON (fastest.userid = times.userid AND fastest.'.$t1.' = times.'.$t1.') WHERE (rankingid='.$ranking['id'].$qRankExt.')'.$qDif.' ORDER BY '.$t1.','.$t2.',played,id ASC)times)times WHERE rank>='.($pb?min($pb['rank'],$rank['rank']):$rank['rank']).($pb?' AND rank<='.max($pb['rank'],$rank['rank']):null);
							foreach (explode(";","SET @rnk=0;SET @rank=0;SET @ct1=0;SET @ct2=0") as $subquery) { mysql_query($subquery); }
							$times = mysql_query($query);
							while ($t = mysql_fetch_array($times)) {
								if (!in_array($t['userid'],$calculated)) {
									$user = dbGetUserById($t['userid']);
									if (!dbUpdateTotal($user['nuser'],$game['nurl'])) {
										showErrorMessage('Failed to update totals for '.$user['ndisplay'].'.');
									}
									$calculated[] = $t['userid'];
								}
							}
						}
					} else {
						$showform = true;
						showErrorMessage('Failed to delete time. Please try again.');
					}
				}
				if ($showform) {
					echo '<form name="deletetime" action="'.getUrl('time-delete','%TIME%',$_GET['t']).'" method="post">';
					echo '<p>Are you sure you want to delete this time?</p>';
					echo '<label><span class="field"><input type="checkbox" name="deleteconfirm" value="1" /><span class="check"></span><span class="checktext">Yup</span></span></label>';
					echo '<label><span class="field"><input type="submit" value="Delete" class="warning" /></span></label>';
					echo '</form>';
				}
			}
			//TIME DISPLAYING
			if ($showtime) {
				//get best time data
				$qDif = " AND difficulty='".$time['difficulty']."'";
				$qRankExt = $ranking['piggyback']>0?' OR rankingid='.$ranking['piggyback']:'';
				$query = 'SELECT * FROM (SELECT *,(@rnk:=@rnk+1)rnk,(@rank:=IF(@ct1=treal AND @ct2=tgame,@rank,@rnk))rank,(@ct1:=treal)nt1,(@ct2:=tgame)nt2 FROM (SELECT times.* FROM times JOIN (SELECT userid,MIN('.$t1.')'.$t1.' FROM times WHERE (rankingid='.$ranking['id'].$qRankExt.')'.$qDif.' GROUP BY userid)fastest ON (fastest.userid = times.userid AND fastest.'.$t1.' = times.'.$t1.') WHERE (rankingid='.$ranking['id'].$qRankExt.')'.$qDif.' ORDER BY '.$t1.','.$t2.',played,id ASC)times)times WHERE userid='.$player['id'].' ORDER BY rank,played,id ASC LIMIT 1';
				//echo '<p>'.$query.'</p>';
				foreach (explode(";","SET @rnk=0;SET @rank=0;SET @ct1=0;SET @ct2=0") as $subquery) {
					mysql_query($subquery) or die('<p>'.mysql_error().'</p>');
				}
				$topTime = mysql_query($query) or die('<p>'.mysql_error().'</p>');
				$topTime = mysql_fetch_array($topTime);
				
				//start outputting page
				if (!$edited) {
					echo '<div id="title">';
					echo dbUserCanEditTimes($account['nuser'],$game['nurl'])||$account['id']==$player['id']?'<p class="actions"><a href="'.getUrl('time-submit-rank','%RANKINGNO%',$ranking['id']).'" class="button">new</a> <a href="'.getUrl('time-edit','%TIME%',$_GET['t']).'" class="button">edit</a> <a href="'.getUrl('time-delete','%TIME%',$_GET['t']).'" class="button-warning">delete</a></p>':null;
					echo strlen($ranking['imgfull'])>0?'<img src="'.$ranking['imgfull'].'" title="'.$ranking['nfull'].'" class="ranking-image" />':null;
					echo '<h1><a href="'.getUrl('ranking',array('%GAME%','%RANKING%'),array($game['nurl'],$ranking['nurl'])).'">'.$ranking['nfull'].'</a> &mdash; <span class="'.$time['difficulty'].'">'.$tagList[$time['difficulty']]['name'].'</span></h1>';
					echo '<h2>'.$tStr1.$tStr2.' by '.$player['ndisplay'].'</h2>';
					echo '</div>';
					echo '<div id="content">';
				}
				echo '<div id="time-info">';
				if (strlen($time['video'])>0) {
					$embedPatterns = array_keys($_EMBED);
					$website = 'website';
					for ($a=0; $a<count($embedPatterns); $a++) {
						if (preg_match("@".$embedPatterns[$a]."@",$time['video'],$matches)) {
							echo str_replace(
								array("%WIDTH%","%HEIGHT%","%USERNAME%","%VIDEO%"),
								array($_SETTINGGLOBAL['embed-width']+$_EMBED[$embedPatterns[$a]]['padw'],$_SETTINGGLOBAL['embed-height']+$_EMBED[$embedPatterns[$a]]['padh'],(isset($matches['username'])?$matches['username']:''),$matches['video']),
								$_EMBED[$embedPatterns[$a]]['code']);
							$website = $_EMBED[$embedPatterns[$a]]['website'];
							$a = count($embedPatterns);
						}
					}
					echo '<p class="vidlink"><a href="'.$time['video'].'">Watch video on '.$website.'</a></p>';
				}
				
				$rank = $time['id']==$topTime['id']?'<span class="rnk'.$topTime['rank'].'">'.nToRank($topTime['rank']).'</span><span class="subpoints"> &mdash; '.$points[min(count($points)-1,$topTime['rank']-1)].'pts</span>':'<span class="none">N/A (not best time)</span>';
				echo '<div class="timelist"><table>';
				echo '<tr class="head"><td class="rank">Rank</td><td class="ver">Ver.</td><td class="date">Date</td></tr>';
				echo '<tr class="row">';
				echo '<td class="rank">'.$rank.'</td>';
				echo '<td class="ver">'.$tagList[$time['version']]['name'].'</td>';
				echo '<td class="date">'.strftime($_SETTINGGLOBAL['date-format'],strtotime($time['played'])).'</td>';
				echo '</tr>';
				echo '</table></div>';
				if (strlen($time['comment'])>0) {
					echo '<h3>Comments</h3>';
					echo '<p>'.$time['comment'].'</p>';
				}
				echo '</div>';
				
				echo '<div id="time-history">';
				echo '<h3>Time History</h3>';
				echo '<div class="timelist"><table>';
				echo '<tr class="head"><td class="time">Time</td><td class="ver">Ver.</td><td class="date">Date</td></tr>';
				$history = mysql_query('SELECT * FROM times WHERE userid='.$time['userid'].' AND rankingid='.$time['rankingid'].' AND difficulty="'.$time['difficulty'].'" ORDER BY played DESC, '.$t1.' ASC LIMIT 10') or die ('<p>'.mysql_error().'</p>');
				while ($hTime = mysql_fetch_array($history)) {
					$uStrT = $_SETTINGGLOBAL['url'].str_replace('%TIME%',$hTime['id'],$_URI['time']);
					$tStrTime1 = sToTime($hTime[$t1]);
					$tStrTime2 = sToTime($hTime[$t2]);
					$tStr1 = '<a href="'.$uStrT.'">'.$tStrTime1.'</a>';
					if ($ranking['timetype']=='tGMONLY'||$ranking['timetype']=='tRLONLY') {
						$tStr2 = '';
					} else {
						$tStr2 = '<span class="subtime">'.$tStrTime2.'</span>';
					}
					echo '<tr class="row'.($time['id']==$hTime['id']?' current':'').'">';
					echo '<td class="time">'.$tStr1.$tStr2.'</td>';
					echo '<td class="ver">'.$tagList[$hTime['version']]['name'].'</td>';
					echo '<td class="date">'.strftime($_SETTINGGLOBAL['date-format'],strtotime($hTime['played'])).'</td>';
					echo '</tr>';
				}
				echo '</table></div>';
				echo '</div>';
				echo '</div>';
			}
		} else {
			showErrorMessage('Invalid time specified. <a href="'.getUrl().'">Please select a proper time</a>.');
		}
	} else 
	echo '</div>';
	
	
	
	include("embed-footer.php");
?>
		</div>
	</body>
</html>