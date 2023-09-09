<div id="footer"><div id="footer-container">
<?php
	//functions
	include_once("lib/functions.php");
	include_once("lib/pbkdf2.php");
	//mysql init
	include_once("lib/connect.php");
	//data
	include_once("data/validation.php");
	include_once("data/settings.php");
	
	echo '<p id="foot-message">Ranking system developed by EverAlert</p>';

	echo '<ul id="foot-menu">';
		echo '<li><a href="'.getUrl('practice').'">Practice Room</a></li>';
		echo '<li><a href="'.getUrl('contact').'">Contact</a></li>';
	echo '</ul>';
?>
</div></div>