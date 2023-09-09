<?php
	//database
	include_once("lib/connect.php");
	include_once("lib/phpmailer/PHPMailerAutoload.php");
	//data
	include_once("data/validation.php");
	include_once("data/settings.php");
	
	
	
	
	function msToTime($time) {
		$hr = floor($time/3600000);
		$mn = floor($time/60000)-$hr*60;
		$sc = floor($time/1000)%60;
		$ms = $time%1000;
		while (strlen($ms)<3) { $ms = "0".$ms; }
		if ($hr==0 && $mn==0) {
			$t = $sc.'.'.$ms;
		} elseif ($hr==0) {
			while (strlen($sc)<2) { $sc = "0".$sc; }
			$t = $mn.':'.$sc.'.'.$ms;
		} else {
			while (strlen($mn)<2) { $mn = "0".$mn; }
			while (strlen($sc)<2) { $sc = "0".$sc; }
			$t = $hr.':'.$mn.':'.$sc.'.'.$ms;
		}
		return $t;
	}
	function msToTimeA($time) {
		$t['hr'] = floor($time/3600000);
		$t['mn'] = floor($time/60000)-$t['hr']*60;
		$t['sc'] = floor($time/1000)%60;
		$t['ms'] = $time%1000;
		while (strlen($t['mn'])<2) { $t['mn'] = "0".$t['mn']; }
		while (strlen($t['sc'])<2) { $t['sc'] = "0".$t['sc']; }
		while (strlen($t['ms'])<3) { $t['ms'] = "0".$t['ms']; }
		return $t;
	}
	
	function sToTime($time) {
		$hr = floor($time/3600);
		$mn = floor($time/60)-$hr*60;
		$sc = floor($time%60);
		while (strlen($sc)<2) { $sc = "0".$sc; }
		if ($hr==0) {
			$t = $mn.':'.$sc;
		} else {
			while (strlen($mn)<2) { $mn = "0".$mn; }
			$t = $hr.':'.$mn.':'.$sc;
		}
		return $t;
	}
	function sToTimeA($time) {
		$t['hr'] = floor($time/3600);
		$t['mn'] = floor($time/60)-$t['hr']*60;
		$t['sc'] = floor($time%60);
		while (strlen($t['sc'])<2) { $t['sc'] = "0".$t['sc']; }
		while (strlen($t['mn'])<2) { $t['mn'] = "0".$t['mn']; }
		return $t;
	}
	function timeToS($time) {
		global $_VALIDATION;
		if (preg_match($_VALIDATION['time'],$time,$m)) {
			$t = 0;
			$t += isset($m['h'])?$m['h']*60*60:0;
			$t += $m['m']*60;
			$t += $m['s'];
			if (isset($m['ms'])) {
				while (strlen($m['ms'])<3) { $m['ms'] .= '0'; }
				$t += $m['ms']/1000;
			}
			return $t;
		} else {
			return -1;
		}
	}
	
	function nToRank($n) {
		if (!is_numeric($n)) { return false; }
		$n = (int)$n;
		if ($n==11||$n==12||$n==13) {
			$rank=$n.'th';
		} elseif ($n%10==1) {
			$rank=$n.'st';
		} elseif ($n%10==2) {
			$rank=$n.'nd';
		} elseif ($n%10==3) {
			$rank=$n.'rd';
		} else {
			$rank=$n.'th';
		}
		return $rank;
	}
	
	function array2dsort($array,$key,$order) {
		$keys = array();
		foreach($array as $row) {
			$keys[] = $row[$key];
		}
		array_multisort($keys,$order,$array);
		return $array;
	}
	
	
	
	function getUrl($id='home',$search='',$replace='') {
		global $_SETTINGGLOBAL,$_URI;
		if (array_key_exists($id,$_URI)) {
			return $_SETTINGGLOBAL['url'].str_replace($search,$replace,$_URI[$id]);
		} else {
			return $_SETTINGGLOBAL['url'].$_URI['home'];
		}
	}
	
	function sendMail($email,$subject,$message) {
		//http://help.1and1.com/e-mail-c37589/standard-e-mail-c37590/getting-started-c85087/e-mail-software-setup-credentials-a616889.html
		if (!isset($email)||!isset($subject)||!isset($message)) {  return false;  }
		
		global $_SETTINGGLOBAL;
		
		$mail = new PHPMailer;
		$mail->isSMTP();
		$mail->Host = $_SETTINGGLOBAL['mail-server-outgoing'];
		$mail->SMTPAuth = true;
		$mail->Username = $_SETTINGGLOBAL['mail-user'];
		$mail->Password = $_SETTINGGLOBAL['mail-pass'];
		$mail->SMTPSecure = $_SETTINGGLOBAL['mail-server-outgoing-encryption'];
		$mail->Port = $_SETTINGGLOBAL['mail-server-outgoing-port'];
		$mail->From = $_SETTINGGLOBAL['mail-email'];
		$mail->FromName = $_SETTINGGLOBAL['mail-name'];
		$mail->addAddress($email);
		$mail->addReplyTo($_SETTINGGLOBAL['mail-email'],$_SETTINGGLOBAL['mail-name']);
		$mail->WordWrap = 50;
		$mail->isHTML(true);
		$mail->Subject = $subject;
		$mail->Body    = $message;
		$mail->AltBody = strip_tags($message);
		if(!$mail->send()) { return false; }
		return true;
	}
	
	
	
	function dbGetTags($keys=true) {
		$k = intval($keys);
		$t = mysql_query('SELECT * FROM tags');
		if (mysql_num_rows($t)>0) {
			$tagList = array();
			while ($tag = mysql_fetch_array($t)) {
				if ($k) {
					$tagList[$tag['tag']] = $tag;
				} else {
					$tagList[] = $tag;
				}
			}
			return $tagList;
		} else {
			return false;
		}
	}
	
	function dbGetUser($user) {
		global $_VALIDATION;
		if (!preg_match('/'.$_VALIDATION['urlplayer'].'/',strtolower($user))) { return false; }
		$u = mysql_query('SELECT * FROM users WHERE nuser="'.$user.'"');
		return mysql_num_rows($u)?mysql_fetch_array($u):false;
	}
	function dbGetUserById($id) {
		$u = mysql_query('SELECT * FROM users WHERE id='.intval($id));
		return mysql_num_rows($u)?mysql_fetch_array($u):false;
	}
	function dbGetAllUsers($keys=false) {
		$k = intval($keys);
		$u = mysql_query('SELECT * FROM users ORDER BY nuser,id ASC');
		if (mysql_num_rows($u)>0) {
			$users = array();
			while ($user = mysql_fetch_array($u)) {
				if ($k) {
					$users[$user['nuser']] = $user;
				} else {
					$users[] = $user;
				}
			}
			return $users;
		} else {
			return false;
		}
	}
	
	function dbGetGame($game) {
		global $_VALIDATION;
		if (!preg_match('/'.$_VALIDATION['urlgame'].'/',strtolower($game))) { return false; }
		$g = mysql_query('SELECT * FROM games WHERE nurl="'.$game.'"');
		return mysql_num_rows($g)?mysql_fetch_array($g):false;
	}
	function dbGetGameById($id) {
		$g = mysql_query('SELECT * FROM games WHERE id='.intval($id));
		return mysql_num_rows($g)?mysql_fetch_array($g):false;
	}
	function dbGetAllGames($keys=false) {
		$k = intval($keys);
		$g = mysql_query('SELECT * FROM games ORDER BY nurl,id ASC');
		if (mysql_num_rows($g)>0) {
			$games = array();
			while ($game = mysql_fetch_array($g)) {
				if ($k) {
					$games[$game['nurl']] = $game;
				} else {
					$games[] = $game;
				}
			}
			return $games;
		} else {
			return false;
		}
	}
	
	function dbGetTime($id) {
		$t = mysql_query('SELECT * FROM times WHERE id='.intval($id));
		return mysql_num_rows($t)?mysql_fetch_array($t):false;
	}
	
	function dbGetTotal($user,$game) {
		$u = dbGetUser($user);
		$g = dbGetGame($game);
		if ($u&&$g) {
			$t = mysql_query('SELECT * FROM totals WHERE userid='.$u['id'].' AND gameid='.$g['id']);
			if (mysql_num_rows($t)>0) {
				return mysql_fetch_array($t);
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	function dbGetAllTotals($game='',$keys=false) {
		if (func_num_args()>0) {
			$k = intval($keys);
			if ($g = dbGetGame($game)) {
				$t = mysql_query('SELECT * FROM totals WHERE gameid='.$g['id'].' ORDER BY id ASC');
				if (mysql_num_rows($t)>0) {
					$totals = array();
					while ($total = mysql_fetch_array($t)) {
						if ($k) {
							$totals[$total['nurl']] = $total;
						} else {
							$totals[] = $total;
						}
					}
					return $totals;
				} else {
					return false;
				}
			} else {
				return false;
			}
		} else {
			$k = intval($keys);
			$t = mysql_query('SELECT * FROM totals ORDER BY gameid,id ASC');
			if (mysql_num_rows($t)>0) {
				$totals = array();
				while ($total = mysql_fetch_array($t)) {
					if ($k) {
						$totals[$total['nurl']] = $total;
					} else {
						$totals[] = $total;
					}
				}
				return $totals;
			} else {
				return false;
			}
		}
		return false;
	}
	function dbGetTimeRank($user,$game) {
		$u = dbGetUser($user);
		$g = dbGetGame($game);
		if ($u&&$g) {
			foreach (explode(";","SET @rnk=0;SET @rank=0;SET @ct=0") as $subquery) {
				mysql_query($subquery) or die('<p>'.mysql_error().'</p>');
			}
			$query = 'SELECT rank FROM (SELECT *,(@rnk:=@rnk+1)rnk,(@rank:=IF(@ct=time,@rank,@rnk))rank,(@ct:=time)nt FROM (SELECT * FROM totals WHERE gameid='.$g['id'].' ORDER BY time ASC)totals)totals WHERE userid='.$u['id'].' ORDER BY gameid ASC';
			if ($tTotal = mysql_fetch_array(mysql_query($query))) {
				return $tTotal['rank'];
			} else {
				return -1;
			}
		} else {
			return -1;
		}
	}
	function dbGetPointRank($user,$game) {
		$u = dbGetUser($user);
		$g = dbGetGame($game);
		if ($u&&$g) {
			foreach (explode(";","SET @rnk=0;SET @rank=0;SET @ct=0") as $subquery) {
				mysql_query($subquery) or die('<p>'.mysql_error().'</p>');
			}
			$query = 'SELECT * FROM (SELECT *,(@rnk:=@rnk+1)rnk,(@rank:=IF(@ct=points,@rank,@rnk))rank,(@ct:=points)nt FROM (SELECT * FROM totals WHERE gameid='.$g['id'].' ORDER BY points DESC)totals)totals WHERE userid='.$u['id'].' ORDER BY gameid ASC';
			if ($tTotal = mysql_fetch_array(mysql_query($query))) {
				return $tTotal['rank'];
			} else {
				return -1;
			}
		} else {
			return -1;
		}
	}
	
	function dbUpdateTotal($user,$game) {
		$c = dbCalculateTotals($user,$game);
		$t = dbGetTotal($user,$game);
		if ($c&&$t) {
			return mysql_query('UPDATE totals SET points='.$c['p'].',time='.$c['tr'].' WHERE id='.$t['id'])?true:false;
		} else {
			return false;
		}
	}
	function dbUpdateAllTotals($game='') {
		$a = array('u'=>0,'t'=>0);
		if (func_num_args()>0) {
			$t = dbGetAllTotals($game);
			foreach ($t as $total) {
				$a['t']+=1;
				$g = dbGetGameById($total['gameid']);
				$u = dbGetUserById($total['userid']);
				if (dbUpdateTotal($u['nuser'],$g['nurl'])) { $a['u']+=1; }
			}
		} else {
			$t = dbGetAllTotals();
			foreach ($t as $total) {
				$a['t']+=1;
				$g = dbGetGameById($total['gameid']);
				$u = dbGetUserById($total['userid']);
				if (dbUpdateTotal($u['nuser'],$g['nurl'])) { $a['u']+=1; }
			}
		}
		return $a;
	}
	
	function dbClearExpiredSessions() {
		mysql_query('DELETE FROM sessions WHERE NOW()>=DATE_ADD(created,INTERVAL expires SECOND)');
	}
	
	function dbLoggedIn() {
		$token = isset($_COOKIE['login'])?$_COOKIE['login']:false;
		if (!$token) { return false; }
		global $_VALIDATION;
		if (preg_match('/'.$_VALIDATION['base64'].'/',$token)) {
			$session = mysql_query('SELECT * FROM sessions WHERE token="'.$token.'" AND type="login"');
			if (mysql_num_rows($session)>0) {
				$loggedin = true;
				$session = mysql_fetch_array($session);
				$user = mysql_query('SELECT * FROM users WHERE id='.$session['userid']);
				$user = mysql_fetch_array($user);
				mysql_query('UPDATE sessions SET created=NOW() WHERE token="'.$token.'"');
				setcookie('login',$_COOKIE['login'],time()+$session['expires']);
				return $user;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	function dbCreateNewSessionToken() {
		$tokenexists = 1;
		while ($tokenexists>0) {
			$token = base64_encode(mcrypt_create_iv(24));
			$tokenexists = mysql_num_rows(mysql_query('SELECT token FROM sessions WHERE token="'.$token.'"'));
		}
		return $token;
	}
	
	function dbUserIsAdmin($user) {
		global $_VALIDATION;
		$u = dbGetUser($user);
		if (!$u) { return false; }
		return $u['admin']?true:false;
	}
	function dbUserIsMod($user,$game) {
		global $_VALIDATION;
		$u = dbGetUser($user);
		if (!$u) { return false; }
		if ($u['admin']) { return true; }
		$g = dbGetGame($game);
		return preg_match('/'.str_replace('%MOD%',$u['id'],$_VALIDATION['modexists']).'/',$g['moderators']);
	}
	function dbUserCanEditTimes($user,$game) {
		return dbUserIsMod($user,$game)||dbUserIsAdmin($user)?true:false;
	}
	
	function dbGetAllAdmins() {
		$admins = array();
		$a = mysql_query('SELECT * FROM users WHERE admin=1 ORDER BY nuser ASC');
		if (!$a || mysql_num_rows($a)==0) { return false; }
		while ($admin = mysql_fetch_array($a)) {
			$admins[] = $admin;
		}
		return $admins;
	}
	
	
	
	
	function dbGetRanking($game,$ranking) {
		if ($g = dbGetGame($game)) {
			global $_VALIDATION;
			if (!preg_match('/'.$_VALIDATION['urlranking'].'/',strtolower($ranking))) { return false; }
			$r = mysql_query('SELECT * FROM rankings WHERE gameid='.$g['id'].' AND nurl="'.$ranking.'"');
			return mysql_num_rows($r)?mysql_fetch_array($r):false;
		} else {
			return false;
		}
	}
	function dbGetRankingById($id) {
		if ($r = mysql_query('SELECT * FROM rankings WHERE id='.intval($id))) {
			return mysql_num_rows($r)?mysql_fetch_array($r):false;
		} else {
			return false;
		}
	}
	function dbGetAllRankings($game='',$keys=false) {
		if (func_num_args()>0) {
			$k = intval($keys);
			if ($g = dbGetGame($game)) {
				$r = mysql_query('SELECT * FROM rankings WHERE gameid='.$g['id'].' ORDER BY id ASC');
				if (mysql_num_rows($r)>0) {
					$rankings = array();
					while ($ranking = mysql_fetch_array($r)) {
						if ($k) {
							$rankings[$ranking['nurl']] = $ranking;
						} else {
							$rankings[] = $ranking;
						}
					}
					return $rankings;
				} else {
					return false;
				}
			} else {
				return false;
			}
		} else {
			$r = mysql_query('SELECT * FROM rankings ORDER BY gameid,id ASC');
			if (mysql_num_rows($r)>0) {
				$rankings = array();
				while ($ranking = mysql_fetch_array($r)) {
					if ($k) {
						$rankings[$ranking['nurl']] = $ranking;
					} else {
						$rankings[] = $ranking;
					}
				}
				return $rankings;
			} else {
				return false;
			}
		}
		return false;
	}
	
	function dbCalculateTotals($user,$game) {
		global $_VALIDATION,$_SETTING;
		$u = dbGetUser($user);
		$g = dbGetGame($game);
		$r = dbGetAllRankings($game);
		if ($u&&$g&&$r) {
			$tTime = 0;
			$tTimeRanked = 0;
			$tPoints = 0;
			foreach ($r as $ranking) {
				//calc points
				$points = explode(",",$g['pointscale']);
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
				//go through each difficulty
				foreach (explode(" ",$g['difficulties']) as $difficulty) {
					$qDif = $difficulty!="dALL"?" AND difficulty='".$difficulty."'":"";
					$qRankExt = $ranking['piggyback']>0?' OR rankingid='.$ranking['piggyback']:'';
					$query = 'SELECT * FROM (SELECT *,(@rnk:=@rnk+1)rnk,(@rank:=IF(@ct1=treal AND @ct2=tgame,@rank,@rnk))rank,(@ct1:=treal)nt1,(@ct2:=tgame)nt2 FROM (SELECT times.* FROM times JOIN (SELECT userid,MIN('.$t1.')'.$t1.' FROM times WHERE (rankingid='.$ranking['id'].$qRankExt.')'.$qDif.' GROUP BY userid)fastest ON (fastest.userid = times.userid AND fastest.'.$t1.' = times.'.$t1.') WHERE (rankingid='.$ranking['id'].$qRankExt.')'.$qDif.' ORDER BY '.$t1.','.$t2.',played,id ASC)times)times WHERE userid='.$u['id'].' ORDER BY rank,played,id ASC LIMIT 1';
					foreach (explode(";","SET @rnk=0;SET @rank=0;SET @ct1=0;SET @ct2=0") as $subquery) { mysql_query($subquery); }
					$time = mysql_fetch_array(mysql_query($query));
					if ($time) {
						$tTime += $difficulty!="dALL"?$time[$t1]:0;
						$tTimeRanked += preg_match('/'.str_replace('%TAG%',$difficulty,$_VALIDATION['tagexists']).'/',$ranking['difficulties'])?$time[$t1]:0;
						$tPoints += preg_match('/'.str_replace('%TAG%',$difficulty,$_VALIDATION['tagexists']).'/',$ranking['difficulties'])?$points[min($time['rank']-1,count($points)-1)]:0;
					} else {
						$tTime += $difficulty!="dALL"?$_SETTING[$g['nurl']]['deftime']:0;
						$tTimeRanked += preg_match('/'.str_replace('%TAG%',$difficulty,$_VALIDATION['tagexists']).'/',$ranking['difficulties'])?$_SETTING[$g['nurl']]['deftime']:0;
					}
				}
			}
			return array('p'=>$tPoints,'t'=>$tTime,'tr'=>$tTimeRanked);
		} else {
			return false;
		}
	}
	
	function dbGetMaxPoints($game) {
		$r = dbGetAllRankings($game);
		$p = 0;
		foreach ($r as $ranking) {
			$p += $ranking['maxpoints']*count(explode(' ',$ranking['difficulties']));
		}
		return $p;
	}
	
	
	
	function showErrorMessage($msg) {
		echo '<p class="error">'.$msg.'</p>';
	}
	function showSuccessMessage($msg) {
		echo '<p class="success">'.$msg.'</p>';
	}
	
	
	
	function twitchApi($query='search/channels?q=shadows%20of%20the%20empire') {
		$ch = curl_init();
		curl_setopt_array(
			$ch, array(
			CURLOPT_URL => 'https://api.twitch.tv/kraken/'.$query,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false
		));
		$out = curl_exec($ch);
		curl_close($ch);
		return json_decode($out,true);
	}
	
	
	
	function nameToColor($name='') {
		if (strlen($name)==0) { return false; }
		$name = sha1(str_pad($name,6,$name));
		$color = strrev(substr(bin2hex($name),0,2)).strrev(substr(bin2hex($name),4,2)).strrev(substr(bin2hex($name),8,2));
		return colorScale($color,40,60);
	}
	function colorScale($color='000000',$minS=50,$minB=50) {
		$rgb = hex2rgb('#'.$color);
		if ($rgb==false) { return false; }
		$minS = $minS%100==0&&$minS>0?100:$minS%100;
		$minB = $minB%100==0&&$minB>0?100:$minB%100;
		//COMPRESS SATURATION
		if ($rgb[0]==$rgb[1] && $rgb[1]==$rgb[2]) {
			//force color pick for greyscale while using
			//exact value to determine saturation of col
			$safe = $rgb[0]%3;
			if ($safe!=0) { $rgb[0] = (int)$rgb[$safe]*(1-$minS/100); }
			if ($safe!=1) { $rgb[1] = (int)$rgb[$safe]*(1-$minS/100); }
			if ($safe!=2) { $rgb[2] = (int)$rgb[$safe]*(1-$minS/100); }
			$rgb[$safe] = 255;
		} else {
			$safe = max($rgb[0],$rgb[1],$rgb[2]);
			if ($safe!=$rgb[0]) { $rgb[0] = (int)$rgb[0]*(1-$minS/100); }
			if ($safe!=$rgb[1]) { $rgb[1] = (int)$rgb[1]*(1-$minS/100); }
			if ($safe!=$rgb[2]) { $rgb[2] = (int)$rgb[2]*(1-$minS/100); }
		}
		//COMPRESS BRIGHTNESS
		$max = max($rgb[0],$rgb[1],$rgb[2]);
		$scale = 255/$max;
		$rgb[0] *= $scale;
		$rgb[1] *= $scale;
		$rgb[2] *= $scale;
		$rgb[0] = (int)($rgb[0]*($max/255*(1-$minB/100)+$minB/100));
		$rgb[1] = (int)($rgb[1]*($max/255*(1-$minB/100)+$minB/100));
		$rgb[2] = (int)($rgb[2]*($max/255*(1-$minB/100)+$minB/100));
		//OUTPUT
		$rgb[0] = dechex($rgb[0]);
		while (strlen($rgb[0])<2) { $rgb[0] = '0'.$rgb[0]; }
		$rgb[1] = dechex($rgb[1]);
		while (strlen($rgb[1])<2) { $rgb[1] = '0'.$rgb[1]; }
		$rgb[2] = dechex($rgb[2]);
		while (strlen($rgb[2])<2) { $rgb[2] = '0'.$rgb[2]; }
		return $rgb[0].$rgb[1].$rgb[2];
	}
	
	
	function hex2rgb($hexStr, $returnAsString = false, $seperator = ',') {
		$hexStr = preg_replace("/[^0-9A-Fa-f]/", '', $hexStr); // Gets a proper hex string
		$rgbArray = array();
		if (strlen($hexStr) == 6) { //If a proper hex code, convert using bitwise operation. No overhead... faster
			$colorVal = hexdec($hexStr);
			$rgbArray[0] = 0xFF & ($colorVal >> 0x10);
			$rgbArray[1] = 0xFF & ($colorVal >> 0x8);
			$rgbArray[2] = 0xFF & $colorVal;
		} elseif (strlen($hexStr) == 3) { //if shorthand notation, need some string manipulations
			$rgbArray[0] = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
			$rgbArray[1] = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
			$rgbArray[2] = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
		} else {
			return false; //Invalid hex color code
		}
		return $returnAsString ? implode($seperator, $rgbArray) : $rgbArray; // returns the rgb string or the associative array
	}
?>