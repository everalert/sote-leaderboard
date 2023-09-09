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
	include("embed-menugame.php");
	
	
	
	
	
	echo '<div id="page">';
	//start
	if (isset($_GET['g'])) {
		$game = dbGetGame($_GET['g']);
		if ($game) {
			//get tags
			$tagList = dbGetTags();
			if (isset($_GET['a']) && $_GET['a']=='rules') {
				//GAME RULES PAGE
				echo '<div id="title"><h1>'.$game['nfull'].' Rules</h1></div>';
				echo '<div id="content">';
				echo '<p>The rules of '.$game['nfull'].' speedrunning. Follow them or risk having your times removed.</p>';
				echo '<div id="game-rules">';
				$sections = array_keys($_SETTING[$game['nurl']]['rules']);
				for ($a=0; $a<count($sections); $a++) {
					echo '<h2>'.$sections[$a].'</h2>';
					echo '<ul>';
					foreach ($_SETTING[$game['nurl']]['rules'][$sections[$a]] as $rule) {
						echo '<li>'.$rule.'</li>';
					}
					echo '</ul>';
				}
				echo '</div></div>';
				
				
				
				
				
			} elseif (isset($_GET['a']) && $_GET['a']=='twitch') {
				//TWITCH DIRECTORY
				echo '<div id="title"><h1>'.$game['nfull'].' on Twitch</h1></div>';
				echo '<div id="content">';
				$channels = twitchApi('search/streams?q='.urlencode($_SETTING[$game['nurl']]['twitch']));
				if (count($channels['streams'])>0) {
					$a = 0;
					foreach ($channels['streams'] as $stream) {
						echo '<div class="stream">';
						echo '<h3><a href="'.$stream['channel']['url'].'">'.$stream['channel']['display_name'].'</a></h3>';
						echo '<p>'.$stream['channel']['status'].'</p>';
						echo '<div class="video"><object type="application/x-shockwave-flash" id="live_embed_player_flash" data="http://www.twitch.tv/widgets/live_embed_player.swf?channel=alaris_villain" bgcolor="#000000"><param name="allowFullScreen" value="true" /><param name="allowScriptAccess" value="always" /><param name="allowNetworking" value="all" /><param name="movie" value="http://www.twitch.tv/widgets/live_embed_player.swf" /><param name="flashvars" value="hostname=www.twitch.tv&channel='.$stream['channel']['name'].'&auto_play='.($a==0?'true':'false').'" /></object></div>';
						echo '<div class="chat"><iframe src="http://www.twitch.tv/'.$stream['channel']['name'].'/chat?popout="></iframe></div>';
						echo '</div>';
						$a++;
					}
				} else {
					echo '<p>No '.$game['nfull'].' streams currently live. Why don\'t you fix that?</p>';
				}
				echo '</div>';
				
				
				
				
			//} elseif (isset($_GET['a']) && $_GET['a']=='stats') {
				//GAME STATISTICS PAGE
				
				
				
				
				
			} elseif (isset($_GET['a']) && $_GET['a']=='leaders') {
				echo '<div id="title"><h1>'.$game['nfull'].' Leaders</h1></div>';
				echo '<div id="content">';
				echo '<p>Top players of '.$game['nfull'].', ordered by total points and total time.</p>';
				//GAME LEADERS PAGE
				$totals = mysql_query('SELECT userid,points,time FROM totals WHERE gameid='.$game['id']) or die('<p>'.mysql_error().'</p>');
				$playerList = array();
				while ($total = mysql_fetch_array($totals)) {
					$user = mysql_query('SELECT nuser,ndisplay,verified FROM users WHERE id="'.$total['userid'].'" LIMIT 1');
					$user = mysql_fetch_array($user);
					$playerList[] = array(
						"points" => $total['points'],
						"time" => $total['time'],
						"id" => $total['userid'],
						"nuser" => $user['nuser'],
						"ndisplay" => $user['ndisplay'],
						"verified" => $user['verified']
					);
				}
				
				echo '<div id="game-leaderpts">';
				echo '<h2>Leaders by Points</h2>';
				echo '<div class="timelist"><table>';
				echo '<tr class="head"><td class="rank"></td><td class="player">Player</td><td class="pts">Pts</td></tr>';
				$playerList = array2dsort($playerList,'points',SORT_DESC);
				$playerKeys = array_keys($playerList);
				$curRank = 0;
				$curTie = 1;
				$prevPoints = 0;
				for ($a=0;$a<count($playerKeys);$a++) {
					echo '<tr class="row">';
					
					if ($playerList[$playerKeys[$a]]['points']!=$prevPoints) {
						$curRank += $curTie;
						$curTie = 1;
						$prevPoints = $playerList[$playerKeys[$a]]['points'];
					} else {
						$curTie += 1;
					}
					echo '<td class="rank rnk'.$curRank.'">'.nToRank($curRank).'</td>';
					
					$uStrP = $_SETTINGGLOBAL['url'].str_replace('%PLAYER%',$playerList[$playerKeys[$a]]['nuser'],$_URI['player']);
					echo '<td class="player"><a href="'.$uStrP.'">'.$playerList[$playerKeys[$a]]['ndisplay'].'</a></td>';
					
					echo '<td class="pts">'.$playerList[$playerKeys[$a]]['points'].'</td>';
					
					echo '</tr>';
				}
				echo '</table></div>';
				echo '</div>';
				
				echo '<div id="game-leadertime">';
				echo '<h2>Leaders by Time</h2>';
				echo '<div class="timelist"><table>';
				echo '<tr class="head"><td class="rank"></td><td class="player">Player</td><td class="time">Time</td></tr>';
				$playerList = array2dsort($playerList,'time',SORT_ASC);
				$playerKeys = array_keys($playerList);
				$curRank = 0;
				$curTie = 1;
				$prevTime = -1;
				for ($a=0;$a<count($playerKeys);$a++) {
					echo '<tr class="row">';
					
					if ($playerList[$playerKeys[$a]]['time']!=$prevTime) {
						$curRank += $curTie;
						$curTie = 1;
						$prevTime = $playerList[$playerKeys[$a]]['time'];
					} else {
						$curTie += 1;
					}
					echo '<td class="rank rnk'.$curRank.'">'.nToRank($curRank).'</td>';
					
					$uStrP = $_SETTINGGLOBAL['url'].str_replace('%PLAYER%',$playerList[$playerKeys[$a]]['nuser'],$_URI['player']);
					echo '<td class="player"><a href="'.$uStrP.'">'.$playerList[$playerKeys[$a]]['ndisplay'].'</a></td>';
					
					echo '<td class="time">'.sToTime($playerList[$playerKeys[$a]]['time']).'</td>';
					
					echo '</tr>';
				}
				echo '</table></div>';
				echo '</div>';
				
				
				
				
				
			//} elseif (isset($_GET['a']) && $_GET['a']=='pointless') {
				//POINTLESS TIMES PAGE
				
				
				
				
				
			} else {
				//GAME LANDING PAGE
				echo '<div id="title"><h1>'.$game['nfull'].' World Records</h1></div>';
				echo '<div id="content">';
				echo '<div id="game-timesheet">';
				echo '<p>These are the best known times for '.$game['nfull'].'.</p>';
				echo '<div class="timelist"><table>';
				echo '<tr class="head"><td class="cat"></td>';
				$totalTime = array();
				foreach (explode(" ",$game['difficulties']) as $difficulty) {
					echo '<td class="dif '.$difficulty.'">'.$tagList[$difficulty]['name'].'</td>';
					$totalTime[$difficulty] = 0;
				}
				echo '</tr>';
				$rankings = dbGetAllRankings($game['nurl']);
				//go through each ranking one by one
				foreach ($rankings as $ranking) {
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
						$query = 'SELECT * FROM (SELECT *,(@rnk:=@rnk+1)rnk,(@rank:=IF(@ct1=treal AND @ct2=tgame,@rank,@rnk))rank,(@ct1:=treal)nt1,(@ct2:=tgame)nt2 FROM (SELECT times.* FROM times JOIN (SELECT userid,MIN('.$t1.')'.$t1.' FROM times WHERE (rankingid='.$ranking['id'].$qRankExt.')'.$qDif.' GROUP BY userid)fastest ON (fastest.userid = times.userid AND fastest.'.$t1.' = times.'.$t1.') WHERE (rankingid='.$ranking['id'].$qRankExt.')'.$qDif.' ORDER BY '.$t1.','.$t2.',played,id ASC)times)times WHERE rank=1 ORDER BY rank,played,id ASC';
						//echo '<p>'.$query.'</p>';
						foreach (explode(";","SET @rnk=0;SET @rank=0;SET @ct1=0;SET @ct2=0") as $subquery) {
							mysql_query($subquery) or die('<p>'.mysql_error().'</p>');
						}
						$times = mysql_query($query) or die('<p>'.mysql_error().'</p>');
						//world record dldllrlrlrlrlrl
						$topTime = mysql_fetch_array($times);
						$ranked = preg_match('/'.str_replace('%TAG%',$difficulty,$_VALIDATION['tagexists']).'/',$ranking['difficulties'])?true:false;
						if ($topTime!=false) {
							$totalTime[$difficulty]+=$topTime[$t1];
							//sorting ties
							$tie = 1;
							while ($time = mysql_fetch_array($times)) {
								$tie += 1;
							}
							$tieStr = $tie.'-way tie'; // maybe change to specify untied instead of nothing in future?
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
							echo '<td class="time'.($ranked?null:' unranked').'">'.$tStr1.$tStr2.'<span class="wrplayers">'.($tie>1?$tieStr:'<a href="'.$uStrP.'" style="color:#'.nameToColor($user['nuser']).';">'.$user['ndisplay'].'</a>').'</span></td>';
						} else {
							echo '<td class="none'.($ranked?null:' unranked').'">No Times</td>';
							$totalTime[$difficulty]+=$_SETTING[$game['nurl']]['deftime'];
						}
					}
					echo '</tr>';
				}
				echo '<tr class="head"><td class="totals">Time Totals</td>';
				foreach (explode(" ",$game['difficulties']) as $difficulty) {
					echo '<td class="total">'.sToTime($totalTime[$difficulty]).'</td>';
				}
				echo '</tr>';
				echo '</table></div>';
				$totalTimeKeys = array_keys($totalTime);
				$tTime = 0;
				foreach ($totalTimeKeys as $key) {
					if ($key!="dALL") {  $tTime += $totalTime[$key];  }
				}
				echo '<div class="timesheet-time"><h2>Total Time</h2><span class="time">'.sToTime($tTime).'</span><span class="note">(Excludes "'.$tagList['dALL']['name'].'")</span></div>';
				echo '</div>';
				echo '</div>';
			}
		} else {
			showErrorMessage('Invalid game specified. <a href="'.$_SETTINGGLOBAL['url'].$_URI['home'].'">Please select a proper leaderboard</a>.');
		}
	} else {
		showErrorMessage('No game specified. <a href="'.$_SETTINGGLOBAL['url'].$_URI['home'].'">Please choose one</a>.');
	}
	echo '</div>';
	
	include("embed-footer.php");
?>
		</div>
	</body>
</html>