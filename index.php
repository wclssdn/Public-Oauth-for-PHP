<?php
$from = $_GET['from'];
$force = $_GET['force'];
$oauthClassname = "{$from}Oauth";
$oauth = new $oauthClassname(); //suggest use factory. best suggest is IOC.
$callback = "user/login/{$from}";
if ($_GET['code'] !== null){
	if (($access = $oauth->getAccessToken($_GET['code'], $callback)) !== false){
		//access token is $access
		//refresh token is $oauth->getRefresh()
	}
	header('Location: /');
}
$oauth->setForce($force);
header('Location: ' . $oauth->getAuthorizeUrl($callback));

function __autoload($classname){
	include "lib/{$classname}.class.php";
}