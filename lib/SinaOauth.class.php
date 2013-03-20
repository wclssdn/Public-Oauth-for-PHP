<?php

/**
 * Sina Oauth Adapter
 * @author wclssdn<ssdn@vip.qq.com>
 */
class SinaOauth extends Oauth {
	/*
	 * (non-PHPdoc) @see Oauth::useragent()
	 */
	protected function useragent() {
		return 'Public OAuth2 v1.0 for Sina by wclssdn';
	}
	/*
	 * (non-PHPdoc) @see Oauth::customHeader()
	 */
	protected function customHeader() {
		$headers = array();
		$this->access && $headers[] = "Authorization: OAuth2 {$this->access}";
		$headers[] = "API-RemoteIP: {$_SERVER['REMOTE_ADDR']}";
		return $headers;
	}
	/*
	 * (non-PHPdoc) @see Oauth::customParams()
	 */
	protected function customParams() {
		return array(
			'source' => $this->key, 
			'access_token' => $this->access);
	}
	/*
	 * (non-PHPdoc) @see Oauth::getAuthorizeUrl()
	 */
	public function getAuthorizeUrl($callback, $responseType = 'code') {
		$params['client_id'] = $this->key;
		$params['redirect_uri'] = $callback;
		$params['response_type'] = $responseType;
		$params['state'] = '';
		$params['display'] = $this->display;
		$params['forcelogin'] = $this->force ? 'true' : 'false';
		return 'https://api.weibo.com/oauth2/authorize?' . http_build_query($params);
	}
	/*
	 * (non-PHPdoc) @see Oauth::getAccessToken()
	 */
	public function getAccessToken($code, $callback) {
		$params['grant_type'] = 'authorization_code';
		$params['code'] = $code;
		$params['redirect_uri'] = $callback;
		$response = $this->post('https://api.weibo.com/oauth2/access_token', $params);
		if (is_array($response) && !isset($response['error'])){
			$this->access = $response['access_token'];
			$this->refresh = isset($response['refresh_token']) ? $response['refresh_token'] : '';
		}
		return $this->access;
	}
	/*
	 * (non-PHPdoc) @see Oauth::refreshAccessToken()
	 */
	public function refreshAccessToken() {
		$params['grant_type'] = 'refresh_token';
		$params['refresh_token'] = $this->refresh;
		$response = $this->post('https://api.weibo.com/oauth2/access_token', $params);
		if (is_array($response) && !isset($response['error'])){
			$this->access = $response['access_token'];
			$this->refresh = isset($response['refresh_token']) ? $response['refresh_token'] : '';
		}
		return $this->access;
	}
}