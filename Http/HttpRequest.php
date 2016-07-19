<?php

namespace Framework\Http;

use Framework\IHttpRequest;

class HttpRequest implements IHttpRequest {
	
	/**
	 *
	 * @var string $mMethod
	 */
	protected $mMethod = 'GET';
	
	/**
	 *
	 * @var string $mUri
	 */
	protected $mUri = '/';
	
	/**
	 *
	 * @var string $mId
	 */
	protected $mId = '';
	
	/**
	 * 通过?号传递的参数
	 *
	 * @var map $mQueryParamMap
	 */
	protected $mQueryParamMap = array();
	
	/**
	 *
	 * @var map $mHeader
	 */
	protected $mHeader = array();
	
	/**
	 *
	 * @var string $mHost
	 */
	protected $mHost = '';
	
	/**
	 *
	 * @var map $mCookie
	 */
	protected $mCookieMap = array();
	
	/**
	 *
	 * @var map $mBody
	 */
	protected $mBodyMap = '';

	public function __construct ($url) {
		$this->setURI($url);
	}

	public function setId ($id) {
		$this->mId = $id;
	}

	public function getId () {
		return $this->mId;
	}

	public function set ($name, $value) {
		$this->mQueryParamMap[$name] = $value;
	}

	public function get ($name) {
		return $this->mQueryParamMap[$name];
	}

	public function setMethod ($method) {
		$this->mMethod = $method;
	}

	public function getMethod () {
		return $this->mMethod;
	}

	public function setURI ($uri) {
		$this->mHost = parse_url($uri, PHP_URL_HOST);
		
		$pos = strpos('?', $uri);
		if (false !== $pos) {
			$this->mUri = substr($uri, 0, $pos);
			$str = substr($uri, $pos + 1);
			$arr = explode('=', $str);
			
			foreach ($arr as $key => $val) {
				$this->mQueryParamMap[$key] = urldecode($val);
			}
		}
	}

	public function getURI () {
		if ('GET' == $this->mMethod) {
		} else {
			return $this->mUri;
		}
	}

	public function setRequestURI ($uri) {
		$this->setURI($uri);
	}

	public function getRequestURI () {
		return $this->getURI();
	}

	public function isAjaxRequest () {
		return strtolower($this->getHeader('HTTP_X_REQUESTED_WITH')) == 'xmlhttprequest';
	}

	public function setHeader ($fieldName, $value) {
		if ('Host' == $fieldName) {
			$this->mHost = $value;
		} else if ('Cookie' == $fieldName) {
			$this->mCookieMap[$fieldName] = $value;
		} else {
			$this->mHeader[$fieldName] = $value;
		}
	}

	public function getHeader ($fieldName) {
		return $this->mHeader[$fieldName];
	}

	public function getClientIP () {
		$IPaddress = $this->getHeader('HTTP_X_FORWARDED_FOR');
		if (! empty($IPaddress)) {
			return $IPaddress;
		}
		
		$IPaddress = $this->getHeader('HTTP_CLIENT_IP');
		if (! empty($IPaddress)) {
			return $IPaddress;
		}
		
		$IPaddress = $this->getHeader('REMOTE_ADDR');
		if (! empty($IPaddress)) {
			return $IPaddress;
		}
	}

	public function getAllParams () {
		return $this->mQueryParamMap;
	}

	public function setCookie ($name, $value = "", $expire = 0, $path = "", $domain = "", $secure = false, $httponly = false) {
		$map = array();
		if (0 != $expire) {
			$map['expire'] = $expire;
		}
		if ('' !== $path) {
			$map['path'] = $path;
		}
		if ('' !== $domain) {
			$map['domain'] = $domain;
		}
		if (true === $secure) {
			$map['secure'] = $secure;
		}
		if (true === $httponly) {
			$map['httponly'] = $httponly;
		}
		if (empty($map)) {
			$this->mCookieMap[$name] = $value;
		} else {
			$this->mCookieMap[$name] = $map;
		}
	}

	public function getCookie ($name) {
		return $this->mCookieMap[$name];
	}

	public function getAllCookie () {
		return $this->mCookieMap;
	}

	public function setBodyMap ($map) {
		$this->mBodyMap = $map;
	}

	public function pack () {
		$s = $this->mMethod . ' ' . $this->mUri . " HTTP/1.1\r\n";
		
		$s .= 'Host: ' . $this->mHost . "\r\n";
		
		foreach ($this->mHeader as $key => $val) {
			$s .= "$key: $val\r\n";
		}
		
		$s .= 'Content-Length: ' . strlen($this->mBodyMap) . "\r\n";
		
		foreach ($this->mCookieMap as $key => $val) {
			$s .= $this->packOneCookie($key, $val);
		}
		
		$s .= "\r\n";
		
		$s .= $this->mBodyMap;
		
		return $s;
	}

	protected function packOneCookie ($key, $cookie) {
		$s = '';
		
		if (is_string($cookie)) {
			$s = "Set-Cookie: $key=" . urlencode($cookie) . "\r\n";
		} else {
			$s = "Set-Cookie: $key=" . urlencode($cookie['value']) . "; ";
			if (0 != $cookie['expire']) {
				$s .= date('D, d-M-Y H:i:s e', cookie['expire']);
			}
			if ('' !== $cookie['path']) {
				$s .= $cookie['path'];
			}
			if ('' !== $cookie['domain']) {
				$s .= $cookie['domain'];
			}
			if (true === $cookie['secure']) {
				$s .= 'secure';
			}
			if (true === $cookie['httponly']) {
				$s .= 'httponly';
			}
			
			$s .= "\r\n";
		}
	}
}