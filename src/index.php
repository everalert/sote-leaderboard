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
	echo '<div id="title"><h1>Shadows of the Empire World Rankings</h1></div>';
	echo '<div id="content">';
	echo '<div id="home-info">';
	echo '<h2>Welcome!</h2>';
	echo '<p>Welcome to the Shadows of the Empire rankings. We like to go fast. If you like to go fast too, feel free to sign up and start submitting times.</p>';
	$news = mysql_query('SELECT * FROM news ORDER BY date DESC LIMIT '.$_SETTINGGLOBAL['home-news-limit']);
	if (mysql_num_rows($news)>0) {
		echo '<h2>News</h2>';
		while ($newsitem = mysql_fetch_array($news)) {
			$date = new DateTime($newsitem['date']);
			echo '<h3>'.$date->format($_SETTINGGLOBAL['date-format-class']).'</h3>';
			echo '<p>'.$newsitem['news'].'</p>';
		}
	}
	echo '</div>';
	
	echo '<div id="home-recent">';
	$times = mysql_query('SELECT * FROM times ORDER BY submitted DESC,played DESC,id DESC LIMIT '.$_SETTINGGLOBAL['home-recent-limit']) or die('<p>'.mysql_error().'</p>');
	if (mysql_num_rows($times)>0) {
		$tagList = dbGetTags();
		echo '<h2>Recent Activity</h2>';
		while ($time = mysql_fetch_array($times)) {
			$player = dbGetUserById($time['userid']);
			$ranking = dbGetRankingById($time['rankingid']);
			$game = dbGetGameById($ranking['gameid']);
			//time priority
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
				$tStr2 = '<span class="subtime">('.$tStrTime2.')</span>';
			}
			//best time
			$toptime = mysql_query('SELECT * FROM times WHERE rankingid='.$time['rankingid'].' AND userid='.$time['userid'].' AND difficulty="'.$time['difficulty'].'" ORDER BY '.$t1.','.$t2.' ASC LIMIT 1') or die('<p>'.mysql_error().'</p>');
			$toptime = mysql_fetch_array($toptime);
			$rankedtime = array();
			if ($toptime['id']==$time['id']) {
				$difInd = preg_match('/'.str_replace('%TAG%',$time['difficulty'],$_VALIDATION['tagexists']).'/',$ranking['difficulties'])?true:false;
				$difAll = preg_match('/'.str_replace('%TAG%','dALL',$_VALIDATION['tagexists']).'/',$ranking['difficulties'])?true:false;
				if (!$difInd&&!$difAll) {
					$rank = '&mdash;';
					$points = '&mdash;';
				} else {
					if ($difInd) {
						$difficulty = $time['difficulty'];
					} else {
						$difficulty = 'dALL';
					}
					//points calc
					$points = explode(",",$game['pointscale']);
					for ($a=0;$a<count($points);$a++) {
						$points[$a] = floor($ranking['maxpoints']*$points[$a]/100);
					}
					//get time data
					$qDif = $difficulty!="dALL"?" AND difficulty='".$difficulty."'":"";
					$qRankExt = $ranking['piggyback']>0?' OR rankingid='.$ranking['piggyback']:'';
					$query = 'SELECT rank FROM (SELECT *,(@rnk:=@rnk+1)rnk,(@rank:=IF(@ct1=treal AND @ct2=tgame,@rank,@rnk))rank,(@ct1:=treal)nt1,(@ct2:=tgame)nt2 FROM (SELECT times.* FROM times JOIN (SELECT userid,MIN('.$t1.')'.$t1.' FROM times WHERE (rankingid='.$ranking['id'].$qRankExt.')'.$qDif.' GROUP BY userid)fastest ON (fastest.userid = times.userid AND fastest.'.$t1.' = times.'.$t1.') WHERE (rankingid='.$ranking['id'].$qRankExt.')'.$qDif.' ORDER BY '.$t1.','.$t2.',played,id ASC)times)times WHERE userid='.$player['id'].' ORDER BY rank,played,id ASC LIMIT 1';
					//echo '<p>'.$query.'</p>';
					foreach (explode(";","SET @rnk=0;SET @rank=0;SET @ct1=0;SET @ct2=0") as $subquery) { mysql_query($subquery); }
					$rankedtime = mysql_query($query);
					$rankedtime = mysql_fetch_array($rankedtime);
					$rank = nToRank($rankedtime['rank']);
					$points = $points[min($rankedtime['rank']-1,count($points)-1)].'pts';
				}
			} else {
				$rank = '&mdash;';
				$points = '&mdash;';
			}
			$date = new DateTime($time['submitted']);
			//output
			echo '<div class="timelist"><table>';
			$uStr = $_SETTINGGLOBAL['url'].str_replace('%PLAYER%',$player['nuser'],$_URI['player']);
			echo '<tr class="head"><td colspan="4"><h3 class="player"><a href="'.$uStr.'" style="color:#'.nameToColor($player['nuser']).';">'.$player['ndisplay'].'</a></h3><h3 class="date">'.$date->format($_SETTINGGLOBAL['date-format-class']).'</h3></td></tr>';
			echo '<tr class="row">';
			$uStr = $_SETTINGGLOBAL['url'].str_replace(array('%RANKING%','%GAME%'),array($ranking['nurl'],$game['nurl']),$_URI['ranking']);
			echo '<td class="cat"><a href="'.$uStr.'">'.$ranking['nfull'].' &mdash; <span class="'.$time['difficulty'].'">'.$tagList[$time['difficulty']]['name'].'</span></td>';
			$uStr = $_SETTINGGLOBAL['url'].str_replace('%TIME%',$time['id'],$_URI['time']);
			echo '<td class="time"><a href="'.$uStr.'">'.$tStr1.'</a>'.$tStr2.'</td>';
			echo '<td class="rank rnk'.$rankedtime['rank'].'">'.$rank.'</td>';
			echo '<td class="pts">'.$points.'</td>';
			echo '</tr>';
			echo '</table></div>';
		}
	}
	echo '</div>';
	echo '</div>';
	echo '</div>';
	
	include("embed-footer.php");
?>
		</div>
	</body>
</html>