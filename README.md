Public-Oauth-for-PHP
====================

Public Oauth 2.0 for PHP

Example:
<pre>
&lt;?php
$from = $_GET['from'];
$force = $_GET['force'];
$oauthClassname = &quot;{$from}Oauth&quot;;
$oauth = new $oauthClassname(); //suggest use factory. best suggest is IOC.
$callback = &quot;user/login/{$from}&quot;;
if ($_GET['code'] !== null){
  if (($access = $oauth-&gt;getAccessToken($_GET['code'], $callback)) !== false){
		//access token is $access
		//refresh token is $oauth-&gt;getRefresh()
	}
	header('Location: /');
}
$oauth-&gt;setForce($force);
header('Location: ' . $oauth-&gt;getAuthorizeUrl($callback));

function __autoload($classname){
	include &quot;lib/{$classname}.class.php&quot;;
}
</pre>
