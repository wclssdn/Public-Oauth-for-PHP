<?php

/**
 * Oauth适配器
 * @author wclssdn<ssdn@vip.qq.com>
 */
abstract class Oauth {

	/**
	 * 应用公钥(appkey)
	 * @var string
	 */
	protected $key;

	/**
	 * 应用密钥(appsecret)
	 * @var string
	 */
	protected $secret;

	/**
	 * access token
	 * @var string
	 */
	protected $access;

	/**
	 * refresh token
	 * @var string
	 */
	protected $refresh;

	/**
	 * api返回格式
	 * @var string
	 */
	protected $format = 'json';

	/**
	 * https方式
	 * @var boolean
	 */
	protected $ssl = false;

	/**
	 * 请求超时时间
	 * @var number
	 */
	protected $timeout = 3;

	/**
	 * 保持回调状态(校验参数)
	 * @var string
	 */
	protected $state;

	/**
	 * 强制显示登录页面
	 * @var boolean
	 */
	protected $force = false;

	/**
	 * 授权页面显示方式
	 * @var string
	 */
	protected $display;

	/**
	 * 自定义userAgent
	 * @return string
	 */
	abstract protected function useragent();

	/**
	 * 自定义header
	 * @return array
	 */
	abstract protected function customHeader();

	/**
	 * 请求api的自定义请求参数
	 * @param array $params
	 * @return array
	 */
	abstract protected function customParams();

	/**
	 * 获取授权URL
	 * @param string $callback 回调地址
	 * @param string $type 授权类型
	 * @return string
	 */
	abstract public function getAuthorizeUrl($callback, $responseType = 'code');

	/**
	 * code方式获取access token
	 * @param string $code
	 * @param string $callback 回跳url
	 */
	abstract public function getAccessToken($code, $callback);

	/**
	 * 刷新access token
	 * @return string
	 */
	abstract public function refreshAccessToken();

	/**
	 * 构造方法
	 * @param string $key
	 * @param string $secret
	 * @param string $access
	 * @param string $refresh
	 */
	public function __construct($key, $secret, $access = '', $refresh = '') {
		$this->key = key;
		$this->secret = $secret;
		$this->access = $access;
		$this->refresh = $refresh;
	}

	/**
	 * 获取refresh token
	 * @return string
	 */
	public function getRefresh() {
		return $this->refresh;
	}

	/**
	 * 是否使用https
	 * @param boolean $ssl
	 */
	public function setSsl($ssl) {
		$this->ssl = (bool)$ssl;
	}

	/**
	 * 是否强制显示登录页面
	 * @param boolean $force
	 */
	public function setForce($force) {
		$this->force = (bool)$force;
	}

	/**
	 * 设置远程请求超时时间(连接超市, 请求超时)
	 * @param number $timeout
	 */
	public function setTimeout($timeout) {
		$this->timeout = (int)$timeout;
	}

	/**
	 * 设置授权页面显示方式
	 * @param string $display
	 */
	public function setDisplay($display) {
		$this->display = $display;
	}

	/**
	 * GET方式请求
	 * @param string $url
	 * @param array $params
	 * @return boolean array
	 */
	public function get($url, array $params) {
		$response = $this->request($url, 'get', $params);
		return $this->unserializeResponse($response);
	}

	/**
	 * POST方式请求
	 * @param string $url
	 * @param array $params
	 * @param boolean $multi 是否上传文件
	 * @return boolean array
	 */
	public function post($url, array $params, $multi = false) {
		$response = $this->request($url, 'post', $params, $multi);
		return $this->unserializeResponse($response);
	}

	/**
	 * DELETE方式请求
	 * @param string $url
	 * @param array $params
	 * @return boolean array
	 */
	public function delete($url, array $params) {
		$response = $this->request($url, 'delete', $params);
		return $this->unserializeResponse($response);
	}

	/**
	 * 请求
	 * @param string $url
	 * @param string $method
	 * @param array $params
	 * @param boolean $multi
	 * @return boolean array
	 */
	protected function request($url, $method = 'get', array $params = array(), $multi = false) {
		switch ($method){
			case 'delete':
			case 'get':
				$url .= '?' . http_build_query($params);
				return $this->curl($url, $method);
			case 'post':
				$headers = array();
				if (!$multi){
					$body = http_build_query($params);
				} else{
					$boundary = uniqid('------------------');
					$body = $this->http_build_multi_body($params, $boundary);
					$headers[] = "Content-Type: multipart/form-data; boundary={$boundary}";
				}
				return $this->curl($url, 'post', $body, $headers);
		}
		return false;
	}

	/**
	 * curl
	 * @param string $url
	 * @param string $method
	 * @param string $body
	 * @param array $headers
	 * @return boolean string
	 */
	protected function curl($url, $method = 'get', $body = '', array $headers = array()) {
		$customHeader = $this->customHeader();
		$headers = array_merge(headers, $customHeader);
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($curl, CURLINFO_HEADER_OUT, true);
		curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		curl_setopt($curl, CURLOPT_USERAGENT, $this->useragent());
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $this->timeout);
		curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_ENCODING, '');
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $this->ssl);
		curl_setopt($curl, CURLOPT_HEADER, false);
		switch ($method){
			case 'get':
				break;
			case 'post':
				curl_setopt($curl, CURLOPT_POST, true);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
				break;
			case 'delete':
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
				break;
		}
		$response = curl_exec($curl);
		curl_close($curl);
		return $response;
	}

	/**
	 * 反序列化结果
	 * @param string $response
	 * @return string
	 */
	protected function unserializeResponse($response) {
		if ($response === false){
			return false;
		}
		switch ($this->format){
			case 'json':
				return json_decode($response, true);
		}
		return null;
	}

	/**
	 * 构建http上传正文
	 * @param array $params
	 * @param string $boundary
	 * @return string
	 */
	protected function http_build_multi_body(array $params, $boundary = '') {
		if (empty($params)){
			return '';
		}
		$body = '';
		foreach ($params as $k => $v){
			if ($v{0} == '@' && is_file(ltrim($v, '@'))){
				$file = ltrim($v, '@');
				$content = file_get_contents($file);
				$minetype = mime_content_type($file);
				$filename = pathinfo($file, PATHINFO_FILENAME);
				$body .= "--{$boundary}\r\n" . 				//
				"Content-Disposition: form-data; name=\"{$k}\"; filename=\"{$filename}\"\r\n" . 				//
				"Content-Type: {$minetype}\r\n\r\n" . 				//
				"{$content}\r\n";
			} else{
				$body .= "--{$boundary}\r\n" . 				//
				"content-Disposition: form-data; name=\"{$k}\"\r\n\r\n" . 				//
				"{$v}\r\n";
			}
		}
		$body .= "--{$boundary}--";
		return $body;
	}
}