<?php

/**
 * Tencent Oauth Adapter
 * @author wclssdn<ssdn@vip.qq.com>
 *
 */
class TencentOauth extends Oauth {

	/*
	 * (non-PHPdoc) @see Oauth::useragent()
	 */
	protected function useragent() {
		return 'Public OAuth2 v1.0 for Tencent by wclssdn';
	}
	/*
	 * (non-PHPdoc) @see Oauth::customHeader()
	 */
	protected function customHeader() {
		return array();
	}
	/*
	 * (non-PHPdoc) @see Oauth::customParams()
	 */
	protected function customParams() {
		return array(
			'oauth_consumer_key' => $this->key, 
			'access_token' => $this->access, 
			'clientip' => $_SERVER['REMOTE_ADDR'], 
			'oauth_version' => '2.a', 
			'scope' => 'all',
			'format' => $this->format
		);
	}
	/*
	 * (non-PHPdoc) @see Oauth::getAuthorizeUrl()
	 */
	public function getAuthorizeUrl($callback, $responseType = 'code') {
		$params['client_id'] = $this->key;
		$params['redirect_uri'] = $callback;
		$params['response_type'] = $responseType;
		$params['forcelogin'] = $this->force;
		$params['type'] = '';
		return 'https://open.t.qq.com/cgi-bin/oauth2/authorize?' . http_build_query($params);
	}
	/*
	 * (non-PHPdoc) @see Oauth::getAccessToken()
	 */
	public function getAccessToken($code, $callback) {
		$params['client_id'] = $this->key;
		$params['client_secret'] = $this->secret;
		$params['grant_type'] = 'authorization_code';
		$params['code'] = $code;
		$params['redirect_uri'] = $callback;
		$r = $this->request('https://open.t.qq.com/cgi-bin/oauth2/access_token?' . http_build_query($params));
		parse_str($r, $out);
		Debug::dump($out);
		if (isset($out['access_token'])){
			$this->access = $out['access_token'];
			$this->refresh = $out['refresh_token'];
			return $this->access;
		}
		return false;
	}
	/*
	 * (non-PHPdoc) @see Oauth::refreshAccessToken()
	 */
	public function refreshAccessToken() {
		$params['client_id'] = $this->key;
		$params['client_secret'] = $this->secret;
		$params['grant_type'] = 'refresh_token';
		$params['refresh_token'] = $this->refresh;
		$r = $this->request('https://open.t.qq.com/cgi-bin/oauth2/access_token?' . http_build_query($params));
		parse_str($r, $out);
		if ($r['access_token']){
			$this->refresh = $out['refresh_token'];
			return $out['access_token'];
		}
		return false;
	}
}