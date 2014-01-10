<?php
if(!defined('IN_KKFRAME')) exit('Access Denied');

class bcms extends mailer{
	var $id = 'bcms';
	var $name = 'BCMS 百度消息队列';
	var $description = '通过 百度消息队列 发邮件 (发送者显示 *******@duapp.com，一般进垃圾箱)';
	var $config = array(
		array('百度消息队列名', 'queue', '一般为随机的字母+数字', ''),
	);
	function isAvailable(){
		return $_SERVER['USER'] == 'bae';
	}
	function send($mail){
		$bcms = new _Bcms();
		$ret = $bcms->mail($this->_get_setting('queue'), '<!--HTML-->'.$mail->message, array($mail->address), array(_Bcms::MAIL_SUBJECT => $mail->subject));
		return $ret !== false;
	}
}

class _Bcms extends _BaeBase{
	const QUEUE_TYPE = 'queue_type';
	const QUEUE_ALIAS_NAME = 'queue_alias_name';
	const FROM = 'from';
	const EFFECT_START = 'effect_start';
	const EFFECT_END = 'effect_end';
	const MSG_ID = 'msg_id';
	const FETCH_NUM = 'fetch_num';
	const MAIL_SUBJECT = 'mail_subject';
	const TIMESTAMP = 'timestamp';
	const EXPIRES = 'expires';
	const VERSION = 'v';
	const QUEUE_NAME = 'queue_name';
	const MSG_TIMEOUT = 'msg_timeout';
	const DESTINATION = 'destination';
	const METHOD = 'method';
	const HOST = 'host';
	const PRODUCT = 'bcms';
	const SIGN = 'sign';
	const ACCESS_TOKEN = 'access_token';
	const SECRET_KEY = 'client_secret';
	const ACCESS_KEY = 'client_id';
	const ADDRESS = 'address';
	const MESSAGE = 'message';
	const LABEL = 'label';
	const USER = 'user';
	const USERTYPE = 'usertype';
	const ACTIONS = 'actions';
	const TOKEN = 'token';
	const DEFAULT_HOST = 'bcms.api.duapp.com';
	private $_clientId = NULL;
	private $_clientSecret = NULL;
	private $_host = NULL;
	private $_requestId = 0;
	private $_curlOpts = array(CURLOPT_TIMEOUT => 30, CURLOPT_CONNECTTIMEOUT => 5);
	const BCMS_SDK_SYS = 1;
	const BCMS_SDK_INIT_FAIL = 2;
	const BCMS_SDK_PARAM = 3;
	const BCMS_SDK_HTTP_STATUS_ERROR_AND_RESULT_ERROR = 4;
	const BCMS_SDK_HTTP_STATUS_OK_BUT_RESULT_ERROR = 5;
	private $_arrayErrorMap = array(
		'0' => 'php sdk error',
		self::BCMS_SDK_SYS => 'php sdk error',
		self::BCMS_SDK_INIT_FAIL => 'php sdk init error',
		self::BCMS_SDK_PARAM => 'lack param',
		self::BCMS_SDK_HTTP_STATUS_ERROR_AND_RESULT_ERROR => 'http status is error, and the body returned is not a json string',
		self::BCMS_SDK_HTTP_STATUS_OK_BUT_RESULT_ERROR => 'http status is ok, but the body returned is not a json string',
		);
	public function mail($queueName, $message, $address, $optional = array()){
		$this->_resetErrorStatus();
		try{
			$tmpArgs = func_get_args();
			$arrArgs = $this->_mergeArgs(array(self::QUEUE_NAME, self::MESSAGE, self::ADDRESS), $tmpArgs);
			$arrArgs [ self::METHOD ] = 'mail';
			$arrArgs [ self::ADDRESS ] = $this->_arrayToString($arrArgs [ self::ADDRESS ]);
			return $this->_commonProcess($arrArgs, array(self::QUEUE_NAME, self::MESSAGE, self::ADDRESS));
		}catch(Exception $ex){
			$this->_bcmsExceptionHandler($ex);
			return false;
		}
	}
	public function __construct($accessKey = NULL, $secretKey = NULL, $host = NULL, $arr_curlOpts = array()){
		if( is_null($accessKey)|| $this->_checkString($accessKey, 1, 64)){
			$this->_clientId = $accessKey;
		}else{
			throw new _BcmsException("invalid param - access key [ ${accessKey} ] , which must be a 1 - 64 length string", self::BCMS_SDK_INIT_FAIL);
		}

		if( is_null($secretKey)|| $this->_checkString($secretKey, 1, 64)){
			$this->_clientSecret = $secretKey;
		}else{
			throw new _BcmsException("invalid param - secret key [ ${secretKey} ] , which must be a 1 - 64 length string", self::BCMS_SDK_INIT_FAIL);
		}

		if( is_null($host)|| $this->_checkString($host, 1, 1024)){
			if(!is_null($host)){
				$this->_host = $host;
			}
		}else{
			throw new _BcmsException("invalid param - host [ ${host} ] , which must be a 1 - 1024 length string", self::BCMS_SDK_INIT_FAIL);
		}

		if(!is_array($arr_curlOpts)){
			throw new _BcmsException( 'invalid param - arr_curlopt is not an array [' . print_r( $arr_curlOpts, true). ']', self::BCMS_SDK_INIT_FAIL);
		}
		foreach($arr_curlOpts as $k => $v){
			$this->_curlOpts [ $k ] = $v;
		}
		$this->_resetErrorStatus();
	}
	private function _checkString($str, $min, $max){
		if(is_string($str)&& strlen($str)>= $min && strlen($str)<= $max){
			return true;
		}
		return false;
	}
	private function _get_ak_sk_host(&$opt, $opt_key, $member, $g_key, $env_key, $min, $max){
		$dis = array('client_id' => 'access_key' , 'client_secret' => 'secret_key', 'host' => 'host');
		global $$g_key;
		if(isset($opt [ $opt_key ])){
			if(! $this->_checkString($opt [ $opt_key ], $min, $max)){
				throw new _BcmsException('invalid ' . $dis [ $opt_key ] . ' in $optinal(' . $opt [ $opt_key ] . '), which must be a ' . $min . ' - ' . $max . ' length string', self::BCMS_SDK_PARAM);
			}
			return ;
		}
		if($this->_checkString($member, $min, $max)){
			$opt [ $opt_key ] = $member;
			return ;
		}
		if(isset($$g_key)){
			if(! $this->_checkString($$g_key, $min, $max)){
				throw new _BcmsException('invalid ' . $g_key . ' in global area(' . $$g_key . '), which must be a ' . $min . ' - ' . $max . ' length string', self::BCMS_SDK_PARAM);
			}
			$opt [ $opt_key ] = $$g_key;
			return ;
		}
		if(false !== getenv($env_key)){
			if(! $this->_checkString(getenv($env_key), $min, $max)){
				throw new _BcmsException('invalid ' . $env_key . ' in environment variable(' . getenv($env_key). '), which must be a ' . $min . ' - ' . $max . ' length string', self::BCMS_SDK_PARAM);
			}
			$opt [ $opt_key ] = getenv($env_key);
			return ;
		}
		if($opt_key === self::HOST){
			$opt [ $opt_key ] = self::DEFAULT_HOST;
			return ;
		}
		throw new _BcmsException('no param(' . $dis [ $opt_key ] . ')was found', self::BCMS_SDK_PARAM);
	}
	private function _adjustOpt(&$opt){
		if(! isset($opt)|| empty($opt)|| ! is_array($opt)){
			throw new _BcmsException('no params are set', self::BCMS_SDK_PARAM);
		}
		if(! isset($opt [ self::TIMESTAMP ])){
			$opt [ self::TIMESTAMP ] = time();
		}

		$this->_get_ak_sk_host($opt, self::ACCESS_KEY, $this->_clientId, 'g_accessKey', 'HTTP_BAE_ENV_AK', 1, 64);
		$this->_get_ak_sk_host($opt, self::SECRET_KEY, $this->_clientSecret, 'g_secretKey', 'HTTP_BAE_ENV_SK', 1, 64);
		$this->_get_ak_sk_host($opt, self::HOST, $this->_host, 'g_host', 'HTTP_BAE_ENV_ADDR_BCMS', 1, 1024);
	}
	private function _bcmsGetSign(&$opt, &$arrContent, $arrNeed = array()) {
		$arrData = array();
		$arrContent = array();
		$arrNeed [ ] = self::TIMESTAMP;
		$arrNeed [ ] = self::METHOD;
		$arrNeed [ ] = self::ACCESS_KEY;
		if(isset($opt [ self::EXPIRES ])){
			$arrNeed [ ] = self::EXPIRES;
		}
		if(isset($opt [ self::VERSION ])){
			$arrNeed [ ] = self::VERSION;
		}
		$arrExclude = array(self::QUEUE_NAME, self::HOST, self::SECRET_KEY);
		foreach($arrNeed as $key){
			if(! isset($opt [ $key ])||(! is_integer( $opt [ $key ])&& empty($opt [ $key ]))){
				throw new _BcmsException("lack param (${key})", self::BCMS_SDK_PARAM);
			}
			if(in_array($key, $arrExclude)){
				continue;
			}
			$arrData [ $key ] = $opt [ $key ] ;
			$arrContent [ $key ] = $opt [ $key ] ;
		}
		foreach($opt as $key => $value){
			if(! in_array($key, $arrNeed)&& ! in_array($key, $arrExclude)){
				$arrData [ $key ] = $value;
				$arrContent [ $key ] = $value;
			}
		}
		ksort($arrData);
		$url = 'http://' . $opt [ self::HOST ] . '/rest/2.0/' . self::PRODUCT . '/';
		if(isset($opt [ self::QUEUE_NAME ])&& !is_null($opt [ self::QUEUE_NAME ])){
			$url .= $opt [ self::QUEUE_NAME ] ;
			$arrContent [ self::QUEUE_NAME ] = $opt [ self::QUEUE_NAME ] ;
		}else if(isset($opt [ self::UID ])&& !is_null($opt [ self::UID ])){
			$url .= $opt [ self::UID ] ;
		}else{
			$url .= 'queue';
		}
		$basicString = 'POST' . $url;
		foreach($arrData as $key => $value){
			$basicString .= $key . '=' . $value;
		}
		$basicString .= $opt [ self::SECRET_KEY ] ;
		$sign = md5(urlencode($basicString));
		$arrContent [ self::SIGN ] = $sign;
		$arrContent [ self::HOST ] = $opt [ self::HOST ];
	}
	private function _baseControl($opt){
		$content = '';
		$resource = 'queue';
		if(isset($opt [ self::QUEUE_NAME ])&& !is_null($opt [ self::QUEUE_NAME ])){
			$resource = $opt [ self::QUEUE_NAME ] ;
			unset( $opt [ self::QUEUE_NAME ]);
		}else if(isset($opt [ self::UID ])&& !is_null($opt [ self::UID ])){
			$resource = $opt [ self::UID ] ;
		}
		$host = $opt [ self::HOST ];
		unset($opt [ self::HOST ]);
		foreach($opt as $k => $v){
			if(is_string($v)){
				$v = urlencode($v);
			}
			$content .= $k . '=' . $v . '&';
		}
		$content = substr($content, 0, strlen($content)- 1);
		$url = 'http://' . $host . '/rest/2.0/' . self::PRODUCT . '/';
		$url .= $resource;
		$request = new RequestCore($url);
		$headers [ 'Content-Type' ] = 'application/x-www-form-urlencoded';
		$headers [ 'User-Agent' ] = 'Baidu Message Service Phpsdk Client';
		foreach($headers as $headerKey => $headerValue){
			$headerValue = str_replace(array("\r", "\n"), '', $headerValue);
			if($headerValue !== ''){
				$request->add_header($headerKey, $headerValue);
			}
		}
		$request->set_method('POST');
		$request->set_body($content);
		if(is_array( $this->_curlOpts)){
			$request->set_curlOpts($this->_curlOpts);
		}
		$request->send_request();
		return new ResponseCore($request->get_response_header(), $request->get_response_body(), $request->get_response_code());
	}
	private function _bcmsExceptionHandler($ex){
		$tmpCode = $ex->getCode();
		if( 0 === $tmpCode){
			$tmpCode = self::BCMS_SDK_SYS;
		}

		$this->errcode = $tmpCode;
		if( $this->errcode >= 30000){
			$this->errmsg = $ex->getMessage();
		}else{
			$this->errmsg = $this->_arrayErrorMap [ $this->errcode ] . ', detail info [ ' . $ex->getMessage(). ', break point: ' . $ex->getFile(). ': ' . $ex->getLine(). ' ] .';
		}
	}
	private function _commonProcess($paramOpt = NULL, $arrNeed = array()){
		$this->_adjustOpt($paramOpt);
		$arrContent = array();
		$this->_bcmsGetSign($paramOpt, $arrContent, $arrNeed);
		$ret = $this->_baseControl($arrContent);
		if(empty($ret)){
			throw new _BcmsException('base control returned empty object', self::BCMS_SDK_SYS);
		}
		if($ret->isOK()){
			$result = json_decode($ret->body, true);
			if(is_null($result)){
				throw new _BcmsException($ret->body, self::BCMS_SDK_HTTP_STATUS_OK_BUT_RESULT_ERROR);
			}
			$this->_requestId = $result [ 'request_id' ];
			return $result;
		}
		$result = json_decode($ret->body, true);
		if(is_null($result)){
			throw new _BcmsException('ret body: ' . $ret->body, self::BCMS_SDK_HTTP_STATUS_ERROR_AND_RESULT_ERROR);
		}
		$this->_requestId = $result [ 'request_id' ];
		throw new _BcmsException($result [ 'error_msg' ], $result [ 'error_code' ]);
	}
	private function _mergeArgs($arrNeed, $tmpArgs){
		$arrArgs = array();
		if(0 == count($arrNeed)&& 0 == count($tmpArgs)){
			return $arrArgs;
		}
		if(count($tmpArgs)- 1 != count($arrNeed)&& count($tmpArgs)!= count($arrNeed)){
			$keys = '(';
					foreach($arrNeed as $key){
					$keys .= $key .= ', ';
					}
					if( $keys [ strlen($keys) - 1 ] === ' ' && ',' === $keys [ strlen($keys) - 2 ]){
					$keys = substr($keys, 0, strlen($keys) - 2);
					}
					$keys .= ')';
			throw new Exception('invalid sdk params, params' . $keys . 'are needed', self::BCMS_SDK_PARAM);
		}
		if(count($tmpArgs)- 1 == count($arrNeed) && ! is_array($tmpArgs [ count($tmpArgs)- 1 ])){
			throw new Exception('invalid sdk params, optional param must be an array', self::BCMS_SDK_PARAM);
		}

		$idx = 0;
		foreach($arrNeed as $key){
			if(! is_integer( $tmpArgs [ $idx ])&& empty($tmpArgs [ $idx ])){
				throw new Exception("lack param (${key})", self::BCMS_SDK_PARAM);
			}
			$arrArgs [ $key ] = $tmpArgs [ $idx ] ;
			$idx += 1;
		}
		if(isset($tmpArgs [ $idx ])){
			foreach($tmpArgs [ $idx ] as $key => $value){
				if(! array_key_exists($key, $arrArgs)&&(is_integer($value) || ! empty($value))){
					$arrArgs [ $key ] = $value;
				}
			}
		}
		if(isset($arrArgs [ self::QUEUE_NAME ])){
			$arrArgs [ self::QUEUE_NAME ] = urlencode($arrArgs [ self::QUEUE_NAME ]);
		}
		return $arrArgs;
	}
	private function _resetErrorStatus(){
		$this->errcode = 0;
		$this->errmsg = $this->_arrayErrorMap [ $this->errcode ] ;
		$this->_requestId = 0;
	}
	function _arrayToString($arr){
    	if(! is_array($arr)){
        	return $arr;
    	}
    	if( 0 === count($arr)){
        	return '[]';
    	}
    	$ret = '[';
    	foreach($arr as $v){
        	$ret .= '"' . $v . '", ';
    	}
    	$ret = substr($ret, 0, strlen($ret) - 2);
    	$ret .= ']';
    	return $ret;
	}
}
class _BaeException extends Exception{}
class _BcmsException extends Exception{}
class _BaeBase{
	public    $errcode;
	public    $errmsg;
	protected $_handle= null;
	public function __construct(){}
	public function error($error_msg, $error_type= E_USER_ERROR){
		echo '<pre>';
		debug_print_backtrace();
		echo '</pre>';
		trigger_error($error_msg, $error_type);
	}
	public function getHandle(){
		return $this->_handle;
	}
	public function errmsg(){
		return $this->errmsg;
	}
	public function errno(){
		return $this->errcode;
	}
}
class RequestCore {
	public $request_url;
	public $request_headers;
	public $request_body;
	public $response;
	public $response_headers;
	public $response_body;
	public $response_code;
	public $response_info;
	public $curl_handle;
	public $method;
	public $proxy = null;
	public $username = null;
	public $password = null;
	public $curlopts = null;
	public $debug_mode = false;
	public $request_class = 'RequestCore';
	public $response_class = 'ResponseCore';
	public $useragent = 'RequestCore/1.4.2';
	public $read_file = null;
	public $read_stream = null;
	public $read_stream_size = null;
	public $read_stream_read = 0;
	public $write_file = null;
	public $write_stream = null;
	public $seek_position = null;
	public $registered_streaming_read_callback = null;
	public $registered_streaming_write_callback = null;
	const HTTP_GET = 'GET';
	const HTTP_POST = 'POST';
	const HTTP_PUT = 'PUT';
	const HTTP_DELETE = 'DELETE';
	const HTTP_HEAD = 'HEAD';
	public function __construct($url = null, $proxy = null, $helpers = null) {
		$this->request_url = $url;
		$this->method = self::HTTP_GET;
		$this->request_headers = array();
		$this->request_body = '';
		if(isset($helpers['request'])&& !empty($helpers['request'])) {
			$this->request_class = $helpers['request'];
		}
		if(isset($helpers['response'])&& !empty($helpers['response'])) {
			$this->response_class = $helpers['response'];
		}
		if($proxy) {
			$this->set_proxy($proxy);
		}
		return $this;
	}
	public function __destruct() {
		if(isset($this->read_file)&& isset($this->read_stream)) {
			fclose($this->read_stream);
		}
		if(isset($this->write_file)&& isset($this->write_stream)) {
			fclose($this->write_stream);
		}
		return $this;
	}
	public function set_credentials($user, $pass) {
		$this->username = $user;
		$this->password = $pass;
		return $this;
	}
	public function add_header($key, $value) {
		$this->request_headers[$key] = $value;
		return $this;
	}
	public function remove_header($key) {
		if(isset($this->request_headers[$key])) {
			unset($this->request_headers[$key]);
		}
		return $this;
	}
	public function set_method($method) {
		$this->method = strtoupper($method);
		return $this;
	}
	public function set_useragent($ua) {
		$this->useragent = $ua;
		return $this;
	}
	public function set_body($body) {
		$this->request_body = $body;
		return $this;
	}
	public function set_request_url($url) {
		$this->request_url = $url;
		return $this;
	}
	public function set_curlopts($curlopts) {
		$this->curlopts = $curlopts;
		return $this;
	}
	public function set_read_stream_size($size) {
		$this->read_stream_size = $size;
		return $this;
	}
	public function set_read_stream($resource, $size = null) {
		if(!isset($size)|| $size < 0) {
			$stats = fstat($resource);
			if($stats && $stats['size'] >= 0) {
				$position = ftell($resource);
				if($position !== false && $position >= 0) {
					$size = $stats['size'] - $position;
				}
			}
		}
		$this->read_stream = $resource;
		return $this->set_read_stream_size($size);
	}
	public function set_read_file($location) {
		$this->read_file = $location;
		$read_file_handle = fopen($location, 'r');
		return $this->set_read_stream($read_file_handle);
	}
	public function set_write_stream($resource) {
		$this->write_stream = $resource;
		return $this;
	}
	public function set_write_file($location) {
		$this->write_file = $location;
		$write_file_handle = fopen($location, 'w');
		return $this->set_write_stream($write_file_handle);
	}
	public function set_proxy($proxy) {
		$proxy = parse_url($proxy);
		$proxy['user'] = isset($proxy['user'])? $proxy['user'] : null;
		$proxy['pass'] = isset($proxy['pass'])? $proxy['pass'] : null;
		$proxy['port'] = isset($proxy['port'])? $proxy['port'] : null;
		$this->proxy = $proxy;
		return $this;
	}
	public function set_seek_position($position) {
		$this->seek_position = isset($position)?(integer)$position : null;
		return $this;
	}
	public function register_streaming_read_callback($callback) {
		$this->registered_streaming_read_callback = $callback;
		return $this;
	}
	public function register_streaming_write_callback($callback) {
		$this->registered_streaming_write_callback = $callback;
		return $this;
	}
	public function streaming_read_callback($curl_handle, $file_handle, $length) {
		if($this->read_stream_read >= $this->read_stream_size) return '';
		if($this->read_stream_read == 0 && isset($this->seek_position)&& $this->seek_position !== ftell($this->read_stream)) {
			if(fseek($this->read_stream, $this->seek_position)!== 0) {
				throw new RequestCore_Exception('The stream does not support seeking and is either not at the requested position or the position is unknown.');
			}
		}
		$read = fread($this->read_stream, min($this->read_stream_size - $this->read_stream_read, $length));
		$this->read_stream_read += strlen($read);
		$out = $read === false ? '' : $read;
		if($this->registered_streaming_read_callback) {
			call_user_func($this->registered_streaming_read_callback, $curl_handle, $file_handle, $out);
		}
		return $out;
	}
	public function streaming_write_callback($curl_handle, $data) {
		$length = strlen($data);
		$written_total = 0;
		$written_last = 0;
		while($written_total < $length){
			$written_last = fwrite($this->write_stream, substr($data, $written_total));
			if($written_last === false) {
				return $written_total;
			}
			$written_total += $written_last;
		}
		if($this->registered_streaming_write_callback) {
			call_user_func($this->registered_streaming_write_callback, $curl_handle, $written_total);
		}
		return $written_total;
	}
	public function prep_request() {
		$curl_handle = curl_init();
		curl_setopt($curl_handle, CURLOPT_URL, $this->request_url);
		curl_setopt($curl_handle, CURLOPT_FILETIME, true);
		curl_setopt($curl_handle, CURLOPT_FRESH_CONNECT, false);
		curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, true);
		curl_setopt($curl_handle, CURLOPT_CLOSEPOLICY, CURLCLOSEPOLICY_LEAST_RECENTLY_USED);
		curl_setopt($curl_handle, CURLOPT_MAXREDIRS, 5);
		curl_setopt($curl_handle, CURLOPT_HEADER, true);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl_handle, CURLOPT_TIMEOUT, 5184000);
		curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 120);
		curl_setopt($curl_handle, CURLOPT_NOSIGNAL, true);
		curl_setopt($curl_handle, CURLOPT_REFERER, $this->request_url);
		curl_setopt($curl_handle, CURLOPT_USERAGENT, $this->useragent);
		curl_setopt($curl_handle, CURLOPT_READFUNCTION, array($this, 'streaming_read_callback'));
		if($this->debug_mode) curl_setopt($curl_handle, CURLOPT_VERBOSE, true);
		if($this->proxy) {
			curl_setopt($curl_handle, CURLOPT_HTTPPROXYTUNNEL, true);
			$host = $this->proxy['host'];
			$host .=($this->proxy['port']) ? ':' . $this->proxy['port'] : '';
			curl_setopt($curl_handle, CURLOPT_PROXY, $host);
			if(isset($this->proxy['user'])&& isset($this->proxy['pass'])) {
				curl_setopt($curl_handle, CURLOPT_PROXYUSERPWD, $this->proxy['user'] . ':' . $this->proxy['pass']);
			}
		}
		if($this->username && $this->password) {
			curl_setopt($curl_handle, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
			curl_setopt($curl_handle, CURLOPT_USERPWD, $this->username . ':' . $this->password);
		}
		if(extension_loaded('zlib')) curl_setopt($curl_handle, CURLOPT_ENCODING, '');
		if(isset($this->request_headers)&& count($this->request_headers)) {
			$temp_headers = array();
			foreach($this->request_headers as $k => $v){
				$temp_headers[] = $k . ': ' . $v;
			}
			curl_setopt($curl_handle, CURLOPT_HTTPHEADER, $temp_headers);
		}
		switch($this->method) {
			case self::HTTP_PUT :
				curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, 'PUT');
				if(isset($this->read_stream)) {
					if(!isset($this->read_stream_size)|| $this->read_stream_size < 0) {
						throw new RequestCore_Exception('The stream size for the streaming upload cannot be determined.');
					}
					curl_setopt($curl_handle, CURLOPT_INFILESIZE, $this->read_stream_size);
					curl_setopt($curl_handle, CURLOPT_UPLOAD, true);
				} else {
					curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $this->request_body);
				}
				break;
			case self::HTTP_POST :
				curl_setopt($curl_handle, CURLOPT_POST, true);
				curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $this->request_body);
				break;
			case self::HTTP_HEAD :
				curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, self::HTTP_HEAD);
				curl_setopt($curl_handle, CURLOPT_NOBODY, 1);
				break;
			default :
				curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, $this->method);
				if(isset($this->write_stream)) {
					curl_setopt($curl_handle, CURLOPT_WRITEFUNCTION, array(
							$this, 'streaming_write_callback'));
					curl_setopt($curl_handle, CURLOPT_HEADER, false);
				} else {
					curl_setopt($curl_handle, CURLOPT_POSTFIELDS, $this->request_body);
				}
				break;
		}
		if(isset($this->curlopts)&& sizeof($this->curlopts)> 0) {
			foreach($this->curlopts as $k => $v){
				curl_setopt($curl_handle, $k, $v);
			}
		}
		return $curl_handle;
	}
	public function process_response($curl_handle = null, $response = null) {
		if($curl_handle && $response) {
			$this->curl_handle = $curl_handle;
			$this->response = $response;
		}
		if(is_resource($this->curl_handle)) {
			$header_size = curl_getinfo($this->curl_handle, CURLINFO_HEADER_SIZE);
			$this->response_headers = substr($this->response, 0, $header_size);
			$this->response_body = substr($this->response, $header_size);
			$this->response_code = curl_getinfo($this->curl_handle, CURLINFO_HTTP_CODE);
			$this->response_info = curl_getinfo($this->curl_handle);
			$this->response_headers = explode("\r\n\r\n", trim($this->response_headers));
			$this->response_headers = array_pop($this->response_headers);
			$this->response_headers = explode("\r\n", $this->response_headers);
			array_shift($this->response_headers);
			$header_assoc = array();
			foreach($this->response_headers as $header){
				$kv = explode(': ', $header);
				$header_assoc[$kv[0]] = $kv[1];
			}
			$this->response_headers = $header_assoc;
			$this->response_headers['_info'] = $this->response_info;
			$this->response_headers['_info']['method'] = $this->method;
			if($curl_handle && $response) {
				return new $this->response_class($this->response_headers, $this->response_body, $this->response_code, $this->curl_handle);
			}
		}
		return false;
	}
	public function send_request($parse = false) {
		$curl_handle = $this->prep_request();
		$this->response = curl_exec($curl_handle);
		if($this->response === false) {
			throw new RequestCore_Exception('cURL resource: ' .(string)$curl_handle . '; cURL error: ' . curl_error($curl_handle). '(' . curl_errno($curl_handle). ')');
		}
		$parsed_response = $this->process_response($curl_handle, $this->response);
		curl_close($curl_handle);
		if($parse) {
			return $parsed_response;
		}
		return $this->response;
	}
	public function send_multi_request($handles, $opt = null) {
		if(count($handles)===0) return array();
		if(!$opt) $opt = array();
		$limit = isset($opt['limit'])? $opt['limit'] : - 1;
		$handle_list = $handles;
		$http = new $this->request_class();
		$multi_handle = curl_multi_init();
		$handles_post = array();
		$added = count($handles);
		$last_handle = null;
		$count = 0;
		$i = 0;
		while($i < $added){
			if($limit > 0 && $i >= $limit) break;
			curl_multi_add_handle($multi_handle, array_shift($handles));
			$i ++;
		}
		do {
			$active = false;
			while(($status = curl_multi_exec($multi_handle, $active)) === CURLM_CALL_MULTI_PERFORM){
				if(count($handles)> 0) break;
			}
			$to_process = array();
			while($done = curl_multi_info_read($multi_handle)) {
				if($done['result'] > 0) {
					throw new RequestCore_Exception('cURL resource: ' .(string)$done['handle'] . '; cURL error: ' . curl_error($done['handle']). '(' . $done['result'] . ')');
				}elseif(!isset($to_process[( int)$done['handle']])) {
					$to_process[( int)$done['handle']] = $done;
				}
			}
			foreach($to_process as $pkey => $done){
				$response = $http->process_response($done['handle'], curl_multi_getcontent($done['handle']));
				$key = array_search($done['handle'], $handle_list, true);
				$handles_post[$key] = $response;
				if(count($handles)> 0) {
					curl_multi_add_handle($multi_handle, array_shift($handles));
				}
				curl_multi_remove_handle($multi_handle, $done['handle']);
				curl_close($done['handle']);
			}
		} while($active || count($handles_post)< $added);
		curl_multi_close($multi_handle);
		ksort($handles_post, SORT_NUMERIC);
		return $handles_post;
	}
	public function get_response_header($header = null) {
		if($header) return $this->response_headers[$header];
		return $this->response_headers;
	}
	public function get_response_body() {
		return $this->response_body;
	}
	public function get_response_code() {
		return $this->response_code;
	}
}
class ResponseCore {
	public $header;
	public $body;
	public $status;
	public function __construct($header, $body, $status = null) {
		$this->header = $header;
		$this->body = $body;
		$this->status = $status;
		return $this;
	}
	public function isOK($codes = array(200, 201, 204, 206)) {
		if(is_array($codes)) {
			return in_array($this->status, $codes);
		}
		return $this->status === $codes;
	}
}
class RequestCore_Exception extends Exception {}
?>