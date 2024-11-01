jQuery(function(){
	//jQuery('#twitter_username').blur(tweetpost_checktwitter);
	//jQuery('#twitter_password').blur(tweetpost_checktwitter);
	jQuery('#twitter_key').blur(tweetpost_checkkey);
	jQuery('#twitter_secret').blur(tweetpost_checkkey);
	jQuery('#bitly_username').blur(tweetpost_checkbitly);
	jQuery('#bitly_apikey').blur(tweetpost_checkbitly);
	jQuery('#supr_username').blur(tweetpost_checksupr);
	jQuery('#supr_apikey').blur(tweetpost_checksupr);
	jQuery('#url_shortener').change(tweetpost_setinput);
	tweetpost_setinput();
});

function tweetpost_setinput () {
	var val = jQuery('#url_shortener').val();
	if (val=='bit.ly') {
		jQuery('#bitly_username').parents('tr').show();
		jQuery('#bitly_apikey').parents('tr').show();
	} else {
		jQuery('#bitly_username').parents('tr').hide();
		jQuery('#bitly_apikey').parents('tr').hide();
	}
	if (val=='su.pr') {
		jQuery('#supr_username').parents('tr').show();
		jQuery('#supr_apikey').parents('tr').show();
	} else {
		jQuery('#supr_username').parents('tr').hide();
		jQuery('#supr_apikey').parents('tr').hide();
	}
}

function tweetpost_checkkey () {
	var key    = jQuery('#twitter_key').val();
	var secret = jQuery('#twitter_secret').val();
	jQuery('#twitter_key ~ div').remove();
	if(!(key && secret)) jQuery('#twitter_key').after("<div>Don't have keys? Get 'em <a href=\"http://dev.twitter.com/apps/new\">here</a>.</div>");
}

function tweetpost_checktwitter () {
	var data = {
		acct: 'twitter',
		user: jQuery('#twitter_username').val(),
		pass: jQuery('#twitter_password').val()
	};
	if(data.user && data.pass)
		tweetpost_verify(data, tweetpost_twitter_result);
	else
		jQuery('#tweetpost_twitterresult').text('Account information incomplete.').css({'background-color':'#FFF','border-color':'#000','border-width':'0px'});
}

function tweetpost_checkbitly () {
	var data = {
		acct: 'bitly',
		login: jQuery('#bitly_username').val(),
		apiKey: jQuery('#bitly_apikey').val()
	};
	jQuery('#bitly_apikey ~ div').remove();
	if(data.login && data.apiKey) {
		jQuery('#bitly_apikey').after('<div>Verifying...</div>');
		tweetpost_verify(data, tweetpost_bitly_result);
	} else {
		jQuery('#bitly_apikey').after('<div>Account information incomplete.</div>').css({'background-color':'#fff','border-color':'#000','border-width':'0px'});
	}
}

function tweetpost_checksupr () {
	var data = {
		acct: 'supr',
		login: jQuery('#supr_username').val(),
		apiKey: jQuery('#supr_apikey').val()
	};
	jQuery('#supr_apikey ~ div').remove();
	if (data.login && data.apiKey) {
		jQuery('#supr_apikey').after('<div>Verifying...</div>');
		tweetpost_verify(data, tweetpost_supr_result);
	} else {
		jQuery('#supr_apikey').after('<div>Account information incomplete.</div>').css({'background-color':'#fff','border-color':'#000','border-width':'0px'});
	}
}

function tweetpost_verify (data, fn) {
	var url = '/wp-content/plugins/tweetpost/account.php';
	var ajax = jQuery.ajax({
		type:'GET',
		data: data,
		url:url,
		dataType: 'json',
		success: fn,
		error: function (a, b, c) {}
	});
}

function tweetpost_result (service, o) {
	jQuery('#'+service+'_apikey ~ div').remove();
	if(0==o.errorCode && 'OK'==o.statusCode)
		jQuery('<div>Account valid.</div>').css({'background-color':'#9FF','border-color':'#6AA','border-width':'1px','width':'300px'}).insertAfter('#'+service+'_apikey');
	else
		jQuery('<div>Invalid account data.</div>').css({'background-color':'#F9F','border-color':'#A6A','border-width':'1px','width':'300px'}).insertAfter('#'+service+'_apikey');
}
function tweetpost_twitter_result (o) {
	tweetpost_result('twitter', o);
}

function tweetpost_bitly_result (o) {
	tweetpost_result('bitly', o);
}

function tweetpost_supr_result (o) {
	tweetpost_result('supr', o);
}
