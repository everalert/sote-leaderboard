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
	if (isset($_GET['g']) && isset($_GET['r'])) {
		//check game is valid
		$game = dbGetGame($_GET['g']);
		if ($game) {
			//check ranking is valid
			$ranking = dbGetRanking($game['nurl'],$_GET['r']);
			if ($ranking) {
				echo '<div id="title">';
				echo dbUserCanEditTimes($account['nuser'],$game['nurl'])||$account?'<p class="actions"><a href="'.getUrl('time-submit-rank','%RANKINGNO%',$ranking['id']).'" class="button">submit time</a></p>':null;
				echo strlen($ranking['imgfull'])>0?'<img src="'.$ranking['imgfull'].'" title="'.$ranking['nfull'].'" class="ranking-image" />':null;
				echo '<h1>'.$ranking['nfull'].'</h1>';
				echo strlen($ranking['description'])>0?'<p class="desc">'.$ranking['description'].'</p>':null;
				echo '</div>';
				
				
				echo '<div id="content">';
				//get general info
				$points = explode(",",$game['pointscale']);
				for ($a=0;$a<count($points);$a++) {
					$points[$a] = floor($ranking['maxpoints']*$points[$a]/100);
				}
				if ($ranking['timetype']=="tREAL"||$ranking['timetype']=="tRLONLY") {
					$t1 = "treal";
					$t2 = "tgame";
				} else {
					$t1 = "tgame";
					$t2 = "treal";
				}
				
				//get tags
				$tagList = dbGetTags();
				
				//get times
				$timeList = array();
				$playerList = array();
				foreach (explode(" ",$game['difficulties']) as $difficulty) {
					$qDif = $difficulty!='dALL'?' AND difficulty="'.$difficulty.'"':'';
					$qRankExt = $ranking['piggyback']>0?' OR rankingid='.$ranking['piggyback']:'';
					$query = 'SELECT * FROM (SELECT *,(@rnk:=@rnk+1)rnk,(@rank:=IF(@ct1=treal AND @ct2=tgame,@rank,@rnk))rank,(@ct1:=treal)nt1,(@ct2:=tgame)nt2 FROM (SELECT times.* FROM times JOIN (SELECT userid,MIN('.$t1.')'.$t1.' FROM times WHERE (rankingid='.$ranking['id'].$qRankExt.')'.$qDif.' GROUP BY userid)fastest ON (fastest.userid = times.userid AND fastest.'.$t1.' = times.'.$t1.') WHERE (rankingid='.$ranking['id'].$qRankExt.')'.$qDif.' ORDER BY '.$t1.','.$t2.',played,id ASC)times)times ORDER BY rank,played,id ASC';
					//echo '<p>'.$query.'</p>';
					foreach (explode(";","SET @rnk=0;SET @rank=0;SET @ct1=0;SET @ct2=0") as $subquery) { mysql_query($subquery); }
					$times = mysql_query($query);
					
					$ranked = preg_match('/'.str_replace('%TAG%',$difficulty,$_VALIDATION['tagexists']).'/',$ranking['difficulties'])?true:false;
					$timeList[] = array(
						"difficulty" => $difficulty,
						"ranked" => $ranked,
						"times" => array()
					);
					while ($time = mysql_fetch_array($times)) {
						$newPoints = $ranked?$points[min(count($timeList[count($timeList)-1]['times']),$time['rank']-1)]:0;
						$user = mysql_query('SELECT * FROM users WHERE id="'.$time['userid'].'" LIMIT 1');
						$user = mysql_fetch_array($user);
						$timeList[count($timeList)-1]['times'][] = array(
							"rank" => $time['rank'],
							"points" => $newPoints,
							"tgame" => $time['tgame'],
							"treal" => $time['treal'],
							"video" => $time['video'],
							"comment" => $time['comment'],
							"id" => $time['id'],
							"nuser" => $user['nuser']
						);
						if (!array_key_exists($user['nuser'],$playerList)) {
							$playerList[$user['nuser']] = array(
								"id" => $user['id'],
								"nuser" => $user['nuser'],
								"ndisplay" => $user['ndisplay'],
								"verified" => $user['verified'],
								"points" => 0
							);
						}
						$playerList[$user['nuser']]['points'] += $newPoints;
					}
				}
				echo '<div id="time-rankings">';
				$timeKeys = array_keys($timeList);
				for ($a=0;$a<count($timeKeys);$a++) {
					//output time rankings
					echo '<div class="ranking">';
					echo '<h2 class="'.$timeList[$timeKeys[$a]]['difficulty'].'">'.$tagList[$timeList[$timeKeys[$a]]['difficulty']]['name'].'</h2>';
					echo '<div class="timelist"><table>';
					echo '<tr class="head"><td class="rank"></td><td class="player">Player</td><td class="time">Time</td><td class="pts">Pts</td><td class="status"></td>';
					echo '</tr>';
					if (count($timeList[$timeKeys[$a]]['times'])>0) {
						foreach ($timeList[$timeKeys[$a]]['times'] as $time) {
							echo '<tr class="row">';
							
							echo '<td class="rank rnk'.$time['rank'].'">'.nToRank($time['rank']).'</td>';
							
							$uStrP = $_SETTINGGLOBAL['url'].str_replace('%PLAYER%',$time['nuser'],$_URI['player']);
							echo '<td class="player"><a href="'.$uStrP.'">'.$playerList[$time['nuser']]['ndisplay'].'</a></td>';
							
							$uStrT = $_SETTINGGLOBAL['url'].str_replace('%TIME%',$time['id'],$_URI['time']);
							$tStrTime1 = sToTime($time[$t1]);
							$tStrTime2 = sToTime($time[$t2]);
							$tStr1 = '<a href="'.$uStrT.'">'.$tStrTime1.'</a>';
							if ($ranking['timetype']=='tGMONLY'||$ranking['timetype']=='tRLONLY') {
								$tStr2 = '';
							} else {
								$tStr2 = '<span class="subtime">('.$tStrTime2.')</span>';
							}
							echo '<td class="time">'.$tStr1.$tStr2.'</td>';
							
							echo '<td class="pts">'.$time['points'].'</td>';
							
							$vStr = strlen($time['video'])>0?'<a href="'.$time['video'].'" title="watch" class="video"><img src="http://puu.sh/8jL3Z.png" /></a>':'<span class="no-video"><img src="http://puu.sh/8jL7S.png" /></span>';
							$cStr = strlen($time['comment'])>0?'<span title="'.$time['comment'].'" class="comment"><img src="http://puu.sh/8jLdv.png" /></span>':'<span class="no-comment"><img src="http://puu.sh/8jLcu.png" /></span>';
							echo '<td class="status">'.$vStr.$cStr.'</td>';
							
							echo '</tr>';
						}
					} else {
						echo '<tr class="row"><td colspan="5" class="none">No submitted times.</tr></td>';
					}
					echo '</table></div>';
					echo '</div>';
				}
				echo '</div>';
				//output player ranking
				echo '<div id="player-ranking">';
				echo '<h2>Players</h2>';
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
					
					$uStrP = $_SETTINGGLOBAL['url'].str_replace('%PLAYER%',$time['nuser'],$_URI['player']);
					echo '<td class="player"><a href="'.$uStrP.'">'.$playerList[$playerKeys[$a]]['ndisplay'].'</a></td>';
					
					echo '<td class="pts">'.$playerList[$playerKeys[$a]]['points'].'</td>';
					
					echo '</tr>';
				}
				echo '</table></div>';
				echo '</div>';
				echo '</div>';
			} else {
				showErrorMessage('Invalid leaderboard specified. <a href="'.getUrl().'">Please select a proper leaderboard</a>.');
			}
		} else {
			showErrorMessage('Invalid game specified. <a href="'.getUrl().'">Please select a proper leaderboard</a>.');
		}
	} else {
		showErrorMessage('No leaderboard specified. <a href="'.getUrl().'">Please choose one</a>.');
	}
	echo '</div>';
	
	include("embed-footer.php");
?>
		</div>
	</body>
</html>