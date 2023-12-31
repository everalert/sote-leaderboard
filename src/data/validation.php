<?php
	$_VALIDATION = array(
		"urlgame" => "^[a-z0-9]+$",
		"urlranking" => "^[a-z0-9]+$",
		"urlplayer" => "^[a-z0-9_]+$",
		"urltime" => "^[0-9]+$",
		"youtube" => "^[a-zA-Z0-9]+$",
		"twitch" => "^[a-zA-Z0-9_]+$",
		"twitter" => "^[a-zA-Z0-9_]+$",
		"tagexists" => "^([a-zA-Z0-9]+ )*%TAG%( [a-zA-Z0-9]+)*$",
		"modexists" => "^([0-9]+ )*%MOD%( [0-9]+)*$",
		"base64" => "^[a-zA-Z0-9\/\-\+\=]+$",
		"email" => "^[a-zA-Z0-9._%+-]+@(?:[a-zA-Z0-9-]+\.)+[a-zA-Z]{2,4}$",
		"avatar" => "^(?:([^:/?#]+):)?(?://([^/?#]*))?([^?#]*\.(?:jpe?g|gif|png))(?:\?([^#]*))?(?:#(.*))?$",
		"time" => "/((?<h>[0-9]+):)*(?<m>[0-9]{1,2}):(?<s>[0-9]{1,2})(\.(?<ms>[0-9]{1,3}))*/"
	);
?>