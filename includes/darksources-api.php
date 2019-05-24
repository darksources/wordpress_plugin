<?php
/*
Welcome to the Dark Sources Security's Password Risk API PHP helper library. 

Please see the attached example file for more information on usage of this file.

The latest copy of this file can be found at https://github.com/darksources/api_php

To unlock the more advanced features or to allow for higher query counts please register at https://billing.darksources.com/signup/
*/

// Uncomment the following lines if including this library with a WordPress plugin.
namespace DarkSources\Helper;
if (! defined('WPINC')) {
	die;
}

if ( basename(__FILE__) == @basename($_SERVER["SCRIPT_FILENAME"]) ) {
	die("This library should not be called directly\n");
}

class DarkSources_API {
	var $api_key;
	var $hash_regex;
	var $base_api_url = 'https://api.darksources.com/v1/';
	var $base_api_dev_url = 'https://api.darksources.com/v1d/';
	var $allowed_hashes = array ( 'plain', 'md2', 'md4', 'md5', 'sha1', 'sha224', 'sha256', 'sha384', 'sha512/224', 'sha512/256', 'sha512', 'sha3-224', 'sha3-256', 'sha3-384', 'sha3-512', 'ripemd128', 'ripemd160', 'ripemd256', 'ripemd320', 'whirlpool', 'tiger128,3', 'tiger160,3', 'tiger192,3', 'tiger128,4', 'tiger160,4', 'tiger192,4', 'snefru', 'snefru256', 'gost', 'gost-crypto', 'adler32', 'crc32', 'crc32b', 'fnv132', 'fnv1a32', 'fnv164', 'fnv1a64', 'joaat', 'haval128,3', 'haval160,3', 'haval192,3', 'haval224,3', 'haval256,3', 'haval128,4', 'haval160,4', 'haval192,4', 'haval224,4', 'haval256,4', 'haval128,5', 'haval160,5', 'haval192,5', 'haval224,5', 'haval256,5', 'php', 'bcrypt', 'blowfishcrypt', 'extdes', 'des', 'phpass', 'md5crypt', 'sha1crypt', 'sha1-256crypt', 'sha1-512crypt', 'wordpress', 'sha1-224', 'sha1-256', 'sha1-384', 'sha1-512', 'sha2-224', 'sha2-256', 'drupal', 'joomla' );
	var $bot_info_submitted = False;
	var $debug = False;
	var $dev = False;

	function __construct() {
		$hash_regex_json = <<<'JSON'
		[
			[ "md5","^[0-9A-Fa-f]{32}$" ],
			[ "sha1","^[a-fA-F0-9]{40}$" ],
			[ "sha224","^[a-fA-F0-9]{56}$" ],
			[ "sha256","^[a-fA-F0-9]{64}$" ],
			[ "sha384","^[a-fA-F0-9]{96}$" ],
			[ "sha512","^[a-fA-F0-9]{128}$" ],
			[ "bcrypt","^\\$2\\$[0-9]\\$([\\w\\.\\\/]+)$" ],
			[ "bcrypt","^\\$2[aA]\\$[0-9]\\$([\\w\\.\\\/]+)$" ],
			[ "bcrypt_openbsd2014","^\\$2[bB]\\$[0-9]\\$([\\w\\.\\\/]+)$" ],
			[ "bcrypt","^\\$2[xX]\\$[0-9]\\$([\\w\\.\\\/]+)$" ],
			[ "bcrypt","^\\$2[yY]\\$[0-9]\\$([\\w\\.\\\/]+)$" ],
			[ "bcrypt","^\\$2[a-zA-Z]\\$[0-9]\\$" ],
			[ "base64_other","^([\\w\\+\\\/]+)=$" ],
			[ "sha1_ast_prefix","^\\*([a-fA-F\\d]){40}$" ],
			[ "crypt","^([\\.\\w\\\/]){13}$" ],
			[ "aes_128_cbc","^\\$AES-128-CBC\\$" ],
			[ "ripemd320","^[A-Fa-f0-9]{80}$" ],
			[ "blowfish_eggdrop","^\\+[a-zA-Z0-9\\\/\\.]{12}$" ],
			[ "blowfish_openbsd","^\\$2a\\$[0-9]{0,2}?\\$[a-zA-Z0-9\\\/\\.]{53}$" ],
			[ "blowfishcrypt","^\\$2[axy]{0,1}\\$[a-zA-Z0-9.\/]{8}\\$[a-zA-Z0-9.\/]{1,}$" ],
			[ "md5_unix","^\\$1\\$.{0,8}\\$[a-zA-Z0-9\\\/\\.]{22}$" ],
			[ "md5_apr","^\\$apr1\\$.{0,8}\\$[a-zA-Z0-9\\\/\\.]{22}$" ],
			[ "md5_mybb","^[a-fA-F0-9]{32}:[a-z0-9]{8}$" ],
			[ "md5_zipmonster","^[a-fA-F0-9]{32}$" ],
			[ "md5crypt","^\\$1\\$[a-zA-Z0-9.\/]{8}\\$[a-zA-Z0-9.\/]{1,}$" ],
			[ "md5_apache_crypt","^\\$apr1\\$[a-zA-Z0-9.\/]{8}\\$[a-zA-Z0-9.\/]{1,}$" ],
			[ "md5_joomla","^[a-fA-F0-9]{32}:[a-zA-Z0-9]{16,32}$" ],
			[ "md5_wordpress","^\\$P\\$[a-zA-Z0-9\\\/\\.]{31}$" ],
			[ "md5_phpBB3","^\\$H\\$[a-zA-Z0-9\\\/\\.]{31}$" ],
			[ "md5_cisco_pix","^[a-zA-Z0-9\\\/\\.]{16}$" ],
			[ "md5_oscommerce","^[a-fA-F0-9]{32}:[a-zA-Z0-9]{2}$" ],
			[ "md5_palshop","^[a-fA-F0-9]{51}$" ],
			[ "d5_ipboard","^[a-fA-F0-9]{32}:.{5}$" ],
			[ "md5_chap","^[a-fA-F0-9]{32}:[0-9]{32}:[a-fA-F0-9]{2}$" ],
			[ "juniper","^[a-zA-Z0-9]{30}:[a-zA-Z0-9]{4,}$" ],
			[ "fortigate","^[a-fA-F0-9]{47}$" ],
			[ "minecraft","^\\$sha\\$[a-zA-Z0-9]{0,16}\\$[a-fA-F0-9]{64}$" ],
			[ "lotus_domino", "^\\(?[a-zA-Z0-9\\+\\\/]{20}\\)?$" ],
			[ "lineage2_c4","^0x[a-fA-F0-9]{32}$" ],
			[ "crc-96_zip","^[a-fA-F0-9]{24}$" ],
			[ "nt_crypt","^\\$3\\$[a-zA-Z0-9.\/]{8}\\$[a-zA-Z0-9.\/]{1,}$" ],
			[ "skein-1024","^[a-fA-F0-9]{256}$" ],
			[ "epi_hash","^0x[A-F0-9]{60}$" ],
			[ "episerver_v1-3","^\\$episerver\\$\\*0\\*[a-zA-Z0-9]{22}==\\*[a-zA-Z0-9\\+]{27}$" ],
			[ "episerver_v4","^\\$episerver\\$\\*1\\*[a-zA-Z0-9]{22}==\\*[a-zA-Z0-9]{43}$" ],
			[ "cisco_ios_sha256","^[a-zA-Z0-9]{43}$" ],
			[ "sha1_django","^sha1\\$.{0,32}\\$[a-fA-F0-9]{40}$" ],
			[ "sha1crypt","^\\$4\\$[a-zA-Z0-9.\/]{8}\\$[a-zA-Z0-9.\/]{1,}$" ],
			[ "sha1_ldap_base64","^\\{sha\\}[a-zA-Z0-9+\/]{27}=$" ],
			[ "sha1_ldap_base64_salt","^\\{ssha\\}[a-zA-Z0-9+\/]{28,}[=]{0,3}$" ],
			[ "sha512_drupal","^\\$S\\$[a-zA-Z0-9\\\/\\.]{52}$" ],
			[ "sha1-512crypt","^\\$6\\$[a-zA-Z0-9.\/]{8}\\$[a-zA-Z0-9.\/]{1,}$" ],
			[ "sha256_django","^sha256\\$.{0,32}\\$[a-fA-F0-9]{64}$" ],
			[ "sha1-256crypt","^\\$5\\$[a-zA-Z0-9.\/]{8}\\$[a-zA-Z0-9.\/]{1,}$" ],
			[ "sha384_django","^sha384\\$.{0,32}\\$[a-fA-F0-9]{96}$" ],
			[ "sha256_unix","^\\$5\\$.{0,22}\\$[a-zA-Z0-9\\\/\\.]{43,69}$" ],
			[ "sha512_unix","^\\$6\\$.{0,22}\\$[a-zA-Z0-9\\\/\\.]{86}$" ],
			[ "ssha1","^({ssha})?[a-zA-Z0-9\\+\\\/]{32,38}?(==)?$" ],
			[ "ssha1_base64","^\\{ssha\\}[a-zA-Z0-9]{32,38}?(==)?$" ],
			[ "ssha512_base64","^\\{ssha512\\}[a-zA-Z0-9+]{96}$" ],
			[ "oracle_11g","^S:[A-Z0-9]{60}$" ],
			[ "smf","^[a-fA-F0-9]{40}:[0-9]{8}&" ],
			[ "mysql_5.x","^\\*[a-f0-9]{40}$" ],
			[ "mysql_3.x","^[a-fA-F0-9]{16}$" ],
			[ "osx_v10.7","^[a-fA-F0-9]{136}$" ],
			[ "osx_v10.8","^\\$ml\\$[a-fA-F0-9$]{199}$" ],
			[ "windows_sam","^[a-fA-F0-9]{32}:[a-fA-F0-9]{32}$" ],
			[ "mssql_2000","^0x0100[a-f0-9]{0,8}?[a-f0-9]{80}$" ],
			[ "mssql_2005","^0x0100[a-f0-9]{0,8}?[a-f0-9]{40}$" ],
			[ "mssql_2012","^0x02[a-f0-9]{0,10}?[a-f0-9]{128}$" ],
			[ "substr_md5($pass),0,16","^[a-fA-F0-9.\/]{16}$" ],
			[ "sha1_oracle","^[a-fA-F0-9]{48}$" ],
			[ "crc32","^[a-fA-F0-9]{8}$" ],
			[ "other","^[a-fA-F0-9]{49,}$" ]
		]
JSON;

		$this->hash_regex = json_decode($hash_regex_json, True);

		$this->api_url = $this->base_api_url;

	}

	public function dev($flag=True) {
		if ($flag === True) {
			$this->api_url = $this->base_api_dev_url;
		} else {
			$this->api_url = $this->base_api_url;
		}
	}

	public function debug($flag=True) {
		$this->debug = $flag;
	}

	public function auth($api_key) {
		$this->api_key = $api_key;
	}

	private function _api_request($command, $data=array()) {
		$data['api_key'] = $this->api_key;


		// Check to see if WordPress API is available to satisfy directory code requirements while still
		// allowing this library to be universal as intended. Note their claim of better speed using
		// the WordPress API does seem to be true against our API. 

		if (function_exists('wp_remote_post')) {
			$post_options = array (
				'body' => $data,
				'timeout' => '5',
				'redirection' => '0',
				'httpversion' => '2.0',
				'blocking' => true,
				'headers' => array(),
				'cookies' => array()
			);

			$st = microtime(True);
			$wr = wp_remote_post($this->api_url . $command . '/', $post_options);
			$et = microtime(True);

			$rc = wp_remote_retrieve_response_code($wr);
			@$r = $wr['body'];

			if ($this->debug == True) {
				error_log("API Requested By: WordPress API");
			}

		} else {
			$c = curl_init();

			curl_setopt($c, CURLOPT_URL, $this->api_url . $command . '/');
			curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 2);
			curl_setopt($c, CURLOPT_TIMEOUT, 5);
			curl_setopt($c, CURLOPT_POST, 1);
			curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 1);
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($c, CURLOPT_POSTFIELDS, $data);

			$st = microtime(True);
			$r = curl_exec($c);
			$et = microtime(True);

			$rc = curl_getinfo($c, CURLINFO_HTTP_CODE);

			if ($this->debug == True) {
				error_log("API Requested By: libCurl API");
			}

		}

		if ($this->debug == True) {
			error_log("API URL: {$this->api_url}${command}/");
			error_log("API Request Code: $rc");
			@error_log("API Time: " . ($et - $st) * 1000 . "ms");
			error_log("API Result:\n$r");
		}

		if ($rc == 200) {
			$ra = json_decode($r, True);


			if (in_array('data', $ra)) {
				$ra['data'] = json_decode($ra['data'], True);
			}

			return($ra);
		} else {
			return(array( 'status_code' => 3, 'status_msg' => array('API Unavailable') ));
		}
	}

	private function get_hash_type($hash) {
		foreach ($this->hash_regex AS $p) {
			if (preg_match('/' . $p[1] . '/', $hash)) {
				return($p[0]);
			}
		}
		return(False);
	}

	private function validate_email ($email) {
		$r = True;

		if ( (! filter_var($email, FILTER_VALIDATE_EMAIL)) and ( (!preg_match('/^([a-fA-F0-9]+)$/', $email)) or (strlen($email) != 40)) ) {
			return('Invalid e-mail address');
		} else {
			return('OK');
		}
	}

	private function validate_hash_type ($hash_type) {
		if (! in_array($hash_type, $this->allowed_hashes)) {
			return('Unsupported hash type');
		} else {
			return('OK');
		}
	}

	public function password_rank_lookup ($password) {
		if ($password == '') {
			return(array( 'status_code' => 1, 'status_msg' => 'Password is required'));
		}

		$data = array(
			'password' => $password
		);


		return($this->_api_request('pr', $data));
	}

	public function email_lookup ($email) {
		$error_msgs = array();

		$r = $this->validate_email($email);
		if ($r != 'OK') {
			array_push($error_msgs, $r);
		}


		if (count($error_msgs) != 0) {
			return(array( 'status_code' => 1, 'status_msg' => implode(",", $error_msgs )));
		}

		$data = array(
			'email' => $email
		);

		return($this->_api_request('el', $data));
	}


	public function domain_rank_lookup ($domain) {
		if ($domain == '') {
			return(array( 'status_code' => 1, 'status_msg' => 'Domain name is required'));
		}

		$data = array(
			'domain' => $domain
		);

		return($this->_api_request('dr', $data));
	}

	public function full_email_lookup ($email) {
		$error_msgs = array();

		if ($this->api_key == '') {
			array_push($error_msgs, 'API key is required for this API call. Please register for service at https://www.darksources.com/');
		}

		$r = $this->validate_email($email);
		if ($r != 'OK') {
			array_push($error_msgs, $r);
		}

		if (count($error_msgs) != 0) {
			return(array( 'status_code' => 1, 'status_msg' => implode(",", $error_msgs )));
		}

		$data = array(
			'email' => $email,
		);

		if (array_key_exists('SERVER_NAME', $_SERVER)) {
			$data['server_host'] = $_SERVER['SERVER_NAME'];
		}

		return($this->_api_request('pro_el', $data));
	}

	public function full_email_password_lookup ($email, $password) {
		$error_msgs = array();

		if ($this->api_key == '') {
			array_push($error_msgs, 'API key is required for this API call. Please register for service at https://www.darksources.com/');
		}

		$r = $this->validate_email($email);
		if ($r != 'OK') {
			array_push($error_msgs, $r);
		}

			if ($password == '') {
					array_push($error_msgs, 'Password is required');
			}

		if (count($error_msgs) != 0) {
			return(array( 'status_code' => 1, 'status_msg' => implode(",", $error_msgs )));
		}

		$data = array(
			'email' => $email,
			'password' => $password
		);

		if (array_key_exists('SERVER_NAME', $_SERVER)) {
			$data['server_host'] = $_SERVER['SERVER_NAME'];
		}

		return($this->_api_request('pro_epl', $data));
	}

	public function full_password_lookup ($password) {
		$error_msgs = array();

		if ($this->api_key == '') {
			array_push($error_msgs, 'API key is required for this API call. Please register for service at https://www.darksources.com/');
		}

		if ($password == '') {
			array_push($error_msgs, 'Password is required');
		}

		if (count($error_msgs) != 0) {
			return(array( 'status_code' => 1, 'status_msg' => implode(",", $error_msgs )));
		}

		$data = array(
			'password' => $password
		);

		return($this->_api_request('pro_pl', $data));
	}

	public function email_hash_lookup ($email, $password_hash, $hash_type=False, $password_salt=False) {
		$error_msgs = array();

		if ($this->api_key == '') {
			array_push($error_msgs, 'API key is required for this API call. Please register for service at https://www.darksources.com/');
		}

		$r = $this->validate_email($email);
		if ($r != 'OK') {
			array_push($error_msgs, $r);
		}

		if (($hash_type == '') or ($hash_type == False)){
			$hash_type = password_get_info($password_hash)['algoName'];
			if ($hash_type == 'unknown') {
				$hash_type = $this->get_hash_type($password_hash);
				if ($hash_type == False) {
					array_push($error_msgs, 'No hash type given and no hash type was found automatically');
				}
			}
		}

		$r = $this->validate_hash_type($hash_type);
		if ($r != 'OK') {
			array_push($error_msgs, $r);
		}

		if ($password_hash == '') {
			array_push($error_msgs, 'Password hash is required');
		}

		if (count($error_msgs) != 0) {
			return(array( 'status_code' => 1, 'status_msg' => implode(",", $error_msgs )));
		}

		$data = array(
			'email' => $email,
			'hash_type' => $hash_type,
			'hash' => $password_hash,
			'salt' => $password_salt
		);

		return($this->_api_request('pro_eh', $data));
	}


	public function bot_check_submit() {
		$error_msgs = array();

		if ($this->api_key == '') {
			array_push($error_msgs, 'API key is required for this API call. Please register for service at https://www.darksources.com/');
		}

		if ($this->bot_info_submitted == True) {
			return(array( 'status_code' => 0, 'status_msg' => 'OK' ));
		}

		if (count($error_msgs) != 0) {
			return(array( 'status_code' => 1, 'status_msg' => implode(",", $error_msgs )));
		}

		$ri = array (
			'request_type' => '',
			'request_time' => '',
			'timezone' => date_default_timezone_get(),
			'request_id' => '',
			'request_ip' => '',
			'request_ip_original' => '',
			'server_host' => '',
			'request_headers' => array(),
			'request_variables' => array(
				'post' => array(),
				'get' => array()
			)
		);
		
		if (array_key_exists('REQUEST_METHOD', $_SERVER)) {
			$ri['request_type'] = strtolower($_SERVER['REQUEST_METHOD']);
		}

		if (array_key_exists('REQUEST_TIME', $_SERVER)) {
			$ri['request_time'] = $_SERVER['REQUEST_TIME'];
		}

		if (array_key_exists('REMOTE_ADDR', $_SERVER)) {
			$ri['request_ip'] = $_SERVER['REMOTE_ADDR'];
			$ri['request_ip_original'] = $_SERVER['REMOTE_ADDR'];
		}

		if (array_key_exists('SERVER_NAME', $_SERVER)) {
			$ri['server_host'] = $_SERVER['SERVER_NAME'];
		}

		if (array_key_exists('UNIQUE_ID', $_SERVER)) {
			$ri['request_id'] = hash('sha1', $_SERVER['UNIQUE_ID']);
		}

		foreach ($_SERVER as $k => $v) {
			if (preg_match('/^HTTP_/', $k)) {
				$new_key = preg_replace('/^http_/', '', strtolower($k));
	
				if (in_array($new_key, array('cookie'))) {
					$ri['request_headers'][$new_key] = '';
				} else {
					$ri['request_headers'][$new_key] = $v;
				}
			}
		}

		if (array_key_exists('cf_connecting_ip', $ri['request_headers'])) {
			$ri['request_ip_original'] = $ri['request_ip'];
			$ri['request_ip'] = $ri['request_headers']['cf_connecting_ip'];
		} elseif (array_key_exists('x_forwarded_for', $ri['request_headers'])) {
			$ri['request_ip_original'] = $ri['request_ip'];
			$ri['request_ip'] = $ri['request_headers']['x_forwarded_for'];
		}

		foreach ($ri['request_headers'] as $k => $v) {
			if (in_array($k, array('x_forwarded_proto', 'x_forwarded_for', 'cf_connecting_ip', 'cf_visitor', 'cf_ray', 'cf_ipcountry', 'upgrade_insecure_requests'))) {
				unset($ri['request_headers'][$k]);
			}
		}

		foreach ($_POST as $k => $v) {
			if (preg_match('/(pass|crypt|secret|hash)/i', $k)) {
				$ri['request_variables']['post'][$k] = '';
			} else {
				$ri['request_variables']['post'][$k] = $v;
			}
		}

		foreach ($_GET as $k => $v) {
			if (preg_match('/(pass|crypt|secret|hash)/i', $k)) {
				$ri['request_variables']['get'][$k] = '';
			} else {
				$ri['request_variables']['get'][$k] = $v;
			}
		}

		$data = array(
			'request_info' => base64_encode(json_encode($ri))
		);

		$this->bot_info_submitted = True;

		return($this->_api_request('pro_botsubmit', $data));
	
	}

	public function keystats () {
		$error_msgs = array();

		if ($this->api_key == '') {
			array_push($error_msgs, 'API key is required for this API call. Please register for service at https://www.darksources.com/');
		}

		if (count($error_msgs) != 0) {
			return(array( 'status_code' => 1, 'status_msg' => implode(",", $error_msgs )));
		}

		$data = array(
		);

		return($this->_api_request('pro_keystats', $data));
	}
}
?>
