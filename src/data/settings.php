<?php
	$_SETTING = array(
		"shadows" => array(
			"colors" => array(
				"dALL" => "8000FF",
				"dEASY" => "00FF00",
				"dMED" => "FFFF00",
				"dHARD" => "FF0000",
				"dJEDI" => "FFFFFF"
			),
			"rules" => array(
				"General" => array(
					"Must play on Nintendo 64 (any region) or the PC version.",
					"Emulators or ROM Injects of any kind are banned.",
					"Use of any codes (including the game's in-built codes) is banned.",
					"All controllers are allowed, provided they don't have features an official controller doesn't."
				),
				"Any% Speedrun" => array(
					"<b>Beat the game (reach the Leia cutscene).</b>",
					"Timing starts when you select Battle of Hoth from the level select.",
					"Timing ends when you lose control at the end of Skyhook Battle.",
					"New file is not required, but strongly preferred."
				),
				"100% Speedrun" => array(
					"<b>Beat the game (reach the Leia cutscene).</b>",
					"<b>Collect all Challenge Points.</b>",
					"Timing starts when you select Battle of Hoth from the level select.",
					"Timing ends when you lose control at the end of Skyhook Battle.",
					"New file is not required, but strongly preferred."
				),
				"Any% Individual Level" => array(
					"<b>Skyhook Battle: Reach the Leia cutscene, then reset the system and show the level time.</b>",
					"<b>All Other Levels: Reach the score screen.</b>",
					"Timed by the in-game level timer."
				),
				"100% Individual Level" => array(
					"<b>Reach the score screen.</b>",
					"<b>Collect all Challenge Points.</b>",
					"Timed by the in-game level timer."
				)
			),
			"deftime" => 86400,
			"twitch" => 'shadows of the empire'
		)
	);
	$_SETTINGGLOBAL = array(
		"url" => "http://localhost/soteranking",
		//"url" => "http://everalert.tv/rankings",
		"embed-width" => 640,
		"embed-height" => 360,
		"date-format" => '%b %d, %Y',
		"date-format-class" => 'M d, Y',
		"player-history-pagesize" => 25,
		"player-profile-recent" => 10,
		"player-pagesize" => 25,
		"avatar-width" => 64,
		"avatar-height" => 64,
		"home-news-limit" => 3,
		"home-recent-limit" => 10,
		"login-fail-limit" => 5,
		"login-session-time" => 300, //in seconds
		"login-session-time-long" => 86400, //in seconds
		"login-session-time-long-text" => "24 hours", //in seconds
		"login-lock-time" => 3600, //in seconds
		"login-lock-text" => "1 hour",
		"passwordreset-limit" => 900, //in seconds
		"passwordreset-text" => "15 minutes",
		"redirect-delay" => 5, //in seconds
		// NOTE: SCRUBBED FOR GITHUB
		"mail-server-incoming" => "",
		"mail-server-incoming-encryption" => "",
		"mail-server-incoming-port" => 0,
		"mail-server-outgoing" => "",
		"mail-server-outgoing-encryption" => "",
		"mail-server-outgoing-port" => 0,
		"mail-email" => "",
		"mail-user" => "",
		"mail-pass" => "",
		"mail-name" => "",
		"recaptcha-key-public" => "",
		"recaptcha-key-private" => "",
		"verification-limit" => 900, //in seconds
		"verification-text" => "15 minutes" //in seconds
	);
	$_URI = array(
		"home" => "/index.php",
		"game" => "/game.php?g=%GAME%",
		"game-rules" => "/game.php?g=%GAME%&a=rules",
		"game-stats" => "/game.php?g=%GAME%&a=stats",
		"game-leaders" => "/game.php?g=%GAME%&a=leaders",
		"game-twitch" => "/game.php?g=%GAME%&a=twitch",
		"game-pointless" => "/game.php?g=%GAME%&a=pointless",
		"ranking" => "/ranking.php?g=%GAME%&r=%RANKING%",
		"player" => "/player.php?p=%PLAYER%",
		"player-list" => "/player.php",
		"player-game" => "/player.php?p=%PLAYER%&g=%GAME%",
		"player-game-history" => "/player.php?p=%PLAYER%&g=%GAME%&a=history",
		"player-game-history-page" => "/player.php?p=%PLAYER%&g=%GAME%&a=history&pg=%PAGE%",
		"player-game-compare" => "/player.php?p=%PLAYER%&g=%GAME%&a=compare",
		"player-game-compare-player" => "/player.php?p=%PLAYER%&g=%GAME%&a=compare&c=%COMPARE%",
		"player-edit" => "/player.php?p=%PLAYER%&a=edit",
		"time" => "/time.php?t=%TIME%",
		"time-edit" => "/time.php?t=%TIME%&a=edit",
		"time-delete" => "/time.php?t=%TIME%&a=delete",
		"time-delete-confirm" => "/time.php?t=%TIME%&a=delete&c=1",
		"time-submit" => "/time.php?a=submit",
		"time-submit-rank" => "/time.php?a=submit&r=%RANKINGNO%",
		"login" => "/login.php",
		"logout" => "/logout.php",
		"signup" => "/signup.php",
		"verify" => "/verify.php",
		"verify-link" => "/verify.php?t=%TOKEN%",
		"contact" => "/contact.php",
		"practice" => "/practice.php",
		"links" => "/links.php",
		"admin" => "/admin.php",
		"admin-news" => "/admin.php?a=news",
		"admin-users" => "/admin.php?a=users",
		"admin-totals" => "/admin.php?a=totals"
	);
	/*$_URI = array(
		"home" => "/",
		"game" => "/%GAME%",
		"game-rules" => "/%GAME%/rules",
		"game-stats" => "/%GAME%/stats",
		"game-leaders" => "/%GAME%/leaders",
		"game-twitch" => "/%GAME%/twitch",
		"game-pointless" => "/%GAME%/pointless",
		"ranking" => "/r/%GAME%/%RANKING%",
		"player" => "/p/%PLAYER%",
		"player-list" => "/players",
		"player-game" => "/p/%PLAYER%/%GAME%",
		"player-game-history" => "/p/%PLAYER%/%GAME%/history",
		"player-game-history-page" => "/p/%PLAYER%/%GAME%/history/%PAGE%",
		"player-game-compare" => "/p/%PLAYER%/%GAME%/compare",
		"player-game-compare-player" => "/p/%PLAYER%/%GAME%/c/%COMPARE%",
		"player-edit" => "/p/%PLAYER%/edit",
		"time" => "/t/%TIME%",
		"time-edit" => "/t/%TIME%/edit",
		"time-delete" => "/t/%TIME%/delete",
		"time-delete-confirm" => "/t/%TIME%/delete/1",
		"time-submit" => "/t/submit",
		"time-submit-rank" => "/t/submit/%RANKINGNO%",
		"login" => "/login",
		"logout" => "/logout",
		"signup" => "/signup",
		"verify" => "/verify",
		"verify-link" => "/verify/%TOKEN%",
		"contact" => "/contact",
		"practice" => "/practice",
		"links" => "/links",
		"admin" => "/admin",
		"admin-news" => "/a/news",
		"admin-users" => "/a/users",
		"admin-totals" => "/a/totals"
	);*/
	$_EMBED = array(
		"^(http(s)?://)?(www\.)?twitch(\.tv)?/(?<user>[a-zA-Z0-9_]+)/c/(?<video>[0-9]+)$" => array(
			"website" => "TwitchTV",
			"username" => "user",
			"video" => "video",
			"padw" => 0,
			"padh" => 31,
			"code" => "<object bgcolor='#000000' data='http://www.twitch.tv/widgets/archive_embed_player.swf' height='%HEIGHT%' id='clip_embed_player_flash' type='application/x-shockwave-flash' width='%WIDTH%'><param name='movie' value='http://www.twitch.tv/widgets/archive_embed_player.swf'><param name='allowScriptAccess' value='always'><param name='allowNetworking' value='all'><param name='allowFullScreen' value='true'><param name='flashvars' value='channel=%USERNAME%&amp;auto_play=false&amp;start_volume=25&amp;chapter_id=%VIDEO%'></object>"
		),
		"^(http(s)?://)?(www\.)?twitch(\.tv)?/(?<user>[a-zA-Z0-9_]+)/b/(?<video>[0-9]+)$" => array(
			"website" => "TwitchTV",
			"username" => "user",
			"video" => "video",
			"padw" => 0,
			"padh" => 31,
			"code" => "<object bgcolor='#000000' data='http://www.twitch.tv/widgets/archive_embed_player.swf' height='%HEIGHT%' id='clip_embed_player_flash' type='application/x-shockwave-flash' width='%WIDTH%'><param name='movie' value='http://www.twitch.tv/widgets/archive_embed_player.swf'><param name='allowScriptAccess' value='always'><param name='allowNetworking' value='all'><param name='allowFullScreen' value='true'><param name='flashvars' value='channel=%USERNAME%&amp;auto_play=false&amp;start_volume=25&amp;archive_id=%VIDEO%'></object>"
		),
		"^(http(s)?://)?(www\.)?youtu(be/|\.be/|be\.com/watch\?v=)(?<video>[a-zA-Z0-9_-]+)(&feature=youtu\.be+)?$" => array(
			"website" => "YouTube",
			"username" => null,
			"video" => "video",
			"padw" => 0,
			"padh" => 3,
			"code" => "<iframe width='%WIDTH%' height='%HEIGHT%' src='//www.youtube.com/embed/%VIDEO%' frameborder='0' allowfullscreen></iframe>"
		)
	)
?>
