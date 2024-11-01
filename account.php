<?php

if (!function_exists('get_post')) {

	$acct = $_GET['acct'];
	if ($acct == 'bitly') {
		$user = $_GET['login'];
		$api = $_GET['apiKey'];
		verify_bitly($user, $api);
	}
	if ($acct == 'twitter') {
		$user = $_GET['user'];
		$pass = $_GET['pass'];
		verify_twitter($user,$pass);
	}
	if ($acct == 'supr') {
		$user = $_GET['login'];
		$api  = $_GET['apiKey'];
		verify_supr($user, $api);
	}
}

function verify_bitly($l, $a) {
	$url = 'http://api.bit.ly/expand?version=2.0.1&shortUrl=http://bit.ly/31IqMl&login='.$l.'&apiKey='.$a;
	$xml = file_get_contents($url);
	die($xml);
}

function verify_supr($l, $a) {
	$url = 'http://su.pr/api/expand?shortUrl=http://su.pr/1SUJk7&login='.$l.'&apiKey='.$a;
	$xml = file_get_contents($url);
	die($xml);
}

function verify_twitter($u,$p) {
	$url = 'http://'.$u.':'.$p.'@twitter.com/account/verify_credentials.xml';
	$xml = @file_get_contents($url);
	if ($xml)
		die('{"errorCode":0, "errorMessage":"", "statusCode":"OK"}');
	else
		die('{"errorCode":1, "errorMessage":"Authentication failed.", "statusCode":"ERROR"}');
}

?>
