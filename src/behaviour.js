$(document).ready(function(){
	var basetime = 250;
	var W = 640;
	var ytH = 363;
	var twH = 391;
	
	//video popup
	$('#page').append(jQuery.parseHTML('<div id="popup-video"><div class="container"><div class="popup"><p class="close"><a href="#" class="button">close</a></p><h2></h2><div class="video"></div><p class="vidlink"></p><p class="info"></p></div></div></div>'));
	$('#popup-video').hide();
	$('.timelist .status .video').click(function(e){
		e.preventDefault();
		e.stopPropagation();
		$('#popup-video h2').html($('#title h1').html()+' &mdash; <span class="'+$(this).parents('.ranking').first().children('h2').first().attr('class')+'">'+$(this).parents('.ranking').first().children('h2').first().html()+'</span>');
		var vidLink = $(this).attr('href');
		if (vidLink.search(/^(http(s)?:\/\/)?(www\.)?youtu(be\/|\.be\/|be\.com\/watch\?v=)([a-zA-Z0-9_-]+)$/)>=0) {
			var embed = vidLink.replace(/^(http(s)?:\/\/)?(www\.)?youtu(be\/|\.be\/|be\.com\/watch\?v=)([a-zA-Z0-9_-]+)$/,
				"<iframe width='"+W+"' height='"+ytH+"' src='//www.youtube.com/embed/$5' frameborder='0' allowfullscreen></iframe>");
			$('#popup-video .video').html(embed);
			$('#popup-video .vidlink').html('<a href="'+vidLink+'">Watch video on YouTube</a>');
		} else if (vidLink.search(/^(http(s)?:\/\/)?(www\.)?twitch(\.tv)?\/([a-zA-Z0-9_]+)\/b\/([0-9]+)$/)>=0) {
			var embed = vidLink.replace(/^(http(s)?:\/\/)?(www\.)?twitch(\.tv)?\/([a-zA-Z0-9_]+)\/b\/([0-9]+)$/,
				"<object bgcolor='#000000' data='http://www.twitch.tv/widgets/archive_embed_player.swf' height='"+twH+"' id='clip_embed_player_flash' type='application/x-shockwave-flash' width='"+W+"'><param name='movie' value='http://www.twitch.tv/widgets/archive_embed_player.swf'><param name='allowScriptAccess' value='always'><param name='allowNetworking' value='all'><param name='allowFullScreen' value='true'><param name='flashvars' value='channel=$5&amp;auto_play=true&amp;archive_id=$6'></object>");
			$('#popup-video .video').html(embed);
			$('#popup-video .vidlink').html('<a href="'+vidLink+'">Watch video on TwitchTV</a>');
		} else if (vidLink.search(/^(http(s)?:\/\/)?(www\.)?twitch(\.tv)?\/([a-zA-Z0-9_]+)\/c\/([0-9]+)$/)>=0) {
			var embed = vidLink.replace(/^(http(s)?:\/\/)?(www\.)?twitch(\.tv)?\/([a-zA-Z0-9_]+)\/c\/([0-9]+)$/,
				"<object bgcolor='#000000' data='http://www.twitch.tv/widgets/archive_embed_player.swf' height='"+twH+"' id='clip_embed_player_flash' type='application/x-shockwave-flash' width='"+W+"'><param name='movie' value='http://www.twitch.tv/widgets/archive_embed_player.swf'><param name='allowScriptAccess' value='always'><param name='allowNetworking' value='all'><param name='allowFullScreen' value='true'><param name='flashvars' value='channel=$5&amp;auto_play=true&amp;chapter_id=$6'></object>");
			$('#popup-video .video').html(embed);
			$('#popup-video .vidlink').html('<a href="'+vidLink+'">Watch video on TwitchTV</a>');
		} else {
			$('#popup-video .video').html('<p>Not sure how to embed this!</p>');
			$('#popup-video .vidlink').html('<a href="'+vidLink+'">Watch video on website</a>');
		}
		$('#popup-video .info').html($(this).parents('td').first().siblings('td.time').first().html()+' &mdash; '+$(this).parents('td').first().siblings('td.player').first().html());
		$('#popup-video').fadeIn(basetime);
	});
	$('#popup-video .close a, #popup-video').click(function(e){
		e.preventDefault();
		e.stopPropagation();
		$('#popup-video').fadeOut(basetime);
	});
	$('#popup-video .popup').click(function(e){
		e.stopPropagation();
	});
	
	//timesheet column width fix
	var difW = 0;
	$('.timelist .dif').each(function(){
		difW = $(this).width()>difW?$(this).width():difW;
	});
	$('.timelist .dif').each(function(){
		$(this).wrapInner('<span style="display:block;width:'+difW+'px;"></span>');
	});
	
	//activity column width fix
	var timeW = 0;
	var rankW = 0;
	var pointW = 0;
	$('#home-recent .timelist').each(function(){
		timeW = $(this).find('td.time').width()>timeW?$(this).find('td.time').width():timeW;
		rankW = $(this).find('td.rank').width()>rankW?$(this).find('td.rank').width():rankW;
		pointW = $(this).find('td.pts').width()>pointW?$(this).find('td.pts').width():pointW;
	});
	$('#home-recent .timelist').each(function(){
		$(this).find('td.time').wrapInner('<span style="display:block;width:'+timeW+'px;"></span>');
		$(this).find('td.rank').wrapInner('<span style="display:block;width:'+rankW+'px;"></span>');
		$(this).find('td.pts').wrapInner('<span style="display:block;width:'+pointW+'px;"></span>');
	});
});