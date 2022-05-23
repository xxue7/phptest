<?php

class Mcurl {
	private $curl;
	public function __construct($url, $header = []) {
		$this->curl = curl_init();
		curl_setopt($this->curl, CURLOPT_URL, $url);
		curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1); // 抓取结果直接返回（如果为0，则直接输出内容到页面）
		curl_setopt($this->curl, CURLOPT_HTTPHEADER, $header);
	}
	public function __destruct() {
		curl_close($this->curl);
	}

	public static function Request($url, $header = [], $data = '', $config = []) {
		$wcurl = new Mcurl($url, $header);
		if (isset($config['time'])) {
			$wcurl->setTimeout($config['time']);
		}
		if (isset($config['cookie'])) {
			$wcurl->setCookie($config['cookie']);
		}
		if (isset($config['ip'])) {
			$wcurl->setIp($config['ip']);
		}
		if (isset($config['rsponseHeader'])) {
			$wcurl->setRsponseHeader($config['rsponseHeader']);
		}
		return empty($data) ? $wcurl->get($config['json'] ?? 0) : $wcurl->post($data, $config['json'] ?? 0);

	}

	public function setTimeout($time = 30) {
		curl_setopt($this->curl, CURLOPT_TIMEOUT, $time);
	}

	public function setIp($ip) {
		$iparr = explode(':', $ip);
		if (count($iparr) == 2) {
			curl_setopt($this->curl, CURLOPT_PROXY, $iparr[0]);
			curl_setopt($this->curl, CURLOPT_PROXYPORT, $iparr[1]);
		}
	}

	public function setCookie($cookie) {
		curl_setopt($this->curl, CURLOPT_COOKIE, $cookie);
	}

	public function setRsponseHeader() {
		curl_setopt($this->curl, CURLOPT_HEADER, 1);
	}

	private function _exec($isJson) {
		$con = curl_exec($this->curl);
		if ($isJson) {
			return json_decode($con, true);
		}
		return $con; //执行并存储结果
	}

	public function get($isJson = 0) {
		return $this->_exec($isJson);
	}

	public function post($data, $isJson = 0) {
		if (is_array($data)) {
			$data = http_build_query($data);
		}
		curl_setopt($this->curl, CURLOPT_POST, 1);
		curl_setopt($this->curl, CURLOPT_POSTFIELDS, $data);
		return $this->_exec($isJson);
	}

	public function getError() {
		return curl_error($this->curl);
	}
	public function close() {
		curl_close($this->curl);
	}

	public static function getResponseCookie($content, $key = '') {
		$cookie = '';
		if (preg_match_all('/Set-Cookie:([^=]+)=([^;]+)/', $content, $matchs)) {
			foreach ($matchs[0] as $value) {
				$ck = str_replace('Set-Cookie: ', '', $value) . ';';
				if (!empty($key)) {
					if (stripos($ck, $key . '=') === 0) {
						return $ck;
					}
					continue;
				}
				$cookie .= $ck;
			}
		}
		return $cookie;
	}

}

?>