<?php
if ( ! defined('QINIU_INCLUDE_PATH')) exit('No direct script access allowed');
/**
 * 部分核心类和函数的定义
 * 
 * @author       Jerry
 * @link            http://blog.icewingcc.com
 * @package    Qiniu
 * @since          Version 1.0
 *
 * 最后更新：2014-4-7
 */

class Qiniu_Exception extends Exception{
	//do nothing
}

/**
 * 发送HTTP请求相关的类
 * 以及CURL操作函数
 */
class Qiniu_request{
	//请求的URL地址
	public $url;
	//请求的内容主题
	public $body;
	//请求包含的header
	public $header = array();
	
	
	function __construct($url, $body = ''){
		$this->url = $url;
		$this->body = $body;
	}
	
	/**
	 * 设置一个HTTP头
	 * @param $name
	 * @param $val
	 */
	function set_header($name, $val){
		$this->header [$name] = $val;
	}
	
	function set_post_data($data){
		
	}
	
	/**
	 * 发送一个HTTP请求，并返回结果对应的Qiniu_response对象
	 * @return Qiniu_response
	 */
// 	protected $_header_temp = '';
	function make_request(){
		$ch = curl_init();
		$options = array(
				CURLOPT_RETURNTRANSFER => true,
//  				CURLOPT_HEADER => true,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_CUSTOMREQUEST  => 'POST',
				CURLOPT_URL => $this->url
		);
		
		if (!empty($this->header)){
			//设置默认的Content-Type头
			if( ! isset($this->header['Content-Type']))
				$this->header['Content-Type'] = 'application/x-www-form-urlencoded';
			
			$header = array();
			foreach($this->header as $key => $parsedUrlValue) {
				$header[] = "$key: $parsedUrlValue";
			}
			$options[CURLOPT_HTTPHEADER] = $header;
		}
		
		if (!empty($this->body)){
			$options[CURLOPT_POSTFIELDS] = $this->body;
		}
		
		
		
		curl_setopt_array($ch, $options);
		$result = curl_exec($ch);
		$ret = curl_errno($ch);
		if ($ret !== 0) {
			throw new Qiniu_Exception('An CURL error has occured, ' . curl_error($ch));
		}
		
		/**
		 * 2014-6-26
		 * 修改此处的传值方式，Response类的构造函数改为接收CURL的返回值
		 */
		$resp = new Qiniu_response($result);	
		
// 		$resp->Body = $result;
// 		$resp->StatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
// 		$resp->Header['Content-Type'] = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
		curl_close($ch);

		return $resp;
	}
	
	
}

/**
 * HTTP响应相关类
 */
class Qiniu_response{
	
	
// 	'code'     => $code,
// 	'body'     => $body,
// 	'headers'  => $headers,
// 	'message'  => $message,
// 	'protocol' => $protocol,
// 	'data'     => $data

	public $code;  //返回码  int
	
	public $body;  //返回的内容 string | json
	
	public $headers = array();  //HTTP头   array
	
	public $message;   
	
	public $protocol; 
	
	public $data = array();  // body  array
	

	/**
	 * 通过CURL返回的内容构造Response对象
	 * @param string $response
	 */
	public function __construct($response){
		if (is_string($response) && ($parsed = $this->parse($response))) {
			foreach ($parsed as $key => $value) {
				$this->{$key} = $value;
			}
		}
	}

	/**
	 * 获取指定的头部信息
	 *
	 * @param string $key
	 * @param string $default
	 * @return string
	 */
	public function header($key, $default = null){
		$key = strtolower($key);
		return !isset($this->headers[$key]) ? $this->headers[$key] : $default;
	}
	
	/**
	 * 解析CURL返回的文本
	 *
	 * @param $response
	 * @return array
	 */
	private function parse($response){
		//解析Header的内容
		$body_pos = strpos($response, "\r\n\r\n");
		$header_string = substr($response, 0, $body_pos);
		if ($header_string == 'HTTP/1.1 100 Continue') {
			$head_pos = $body_pos + 4;
			$body_pos = strpos($response, "\r\n\r\n", $head_pos);
			$header_string = substr($response, $head_pos, $body_pos - $head_pos);
		}
		$header_lines = explode("\r\n", $header_string);
	
		$headers = array();
		$code = false;
		$body = false;
		$protocol = null;
		$message = null;
		$data = array();
	
		foreach ($header_lines as $index => $line) {
			if ($index === 0) {
				preg_match('/^(HTTP\/\d\.\d) (\d{3}) (.*?)$/', $line, $match);
				list(, $protocol, $code, $message) = $match;
				$code = (int)$code;
				continue;
			}
			list($key, $value) = explode(":", $line);
			$headers[strtolower(trim($key))] = trim($value);
		}
	
		if (is_numeric($code)) {
			$body_string = substr($response, $body_pos + 4);
			if (!empty($headers['transfer-encoding']) && $headers['transfer-encoding'] == 'chunked') {
				$body = $this->decodeChunk($body_string);
			} else {
				$body = (string)$body_string;
			}
			$result['header'] = $headers;
		}
	
		// 自动解析数据
		if (strpos($headers['content-type'], 'json')) {
			$data = json_decode($body, true);
		}
	
		return $code ? array(
				'code'     => $code,
				'body'     => $body,
				'headers'  => $headers,
				'message'  => $message,
				'protocol' => $protocol,
				'data'     => $data
		) : false;
	}
	
	/**
	 * Decode chunk
	 *
	 * @param $str
	 * @return string
	 */
	private function decodeChunk($str){
		$body = '';
		while ($str) {
			$chunk_pos = strpos($str, "\r\n") + 2;
			$chunk_size = hexdec(substr($str, 0, $chunk_pos));
			$str = substr($str, $chunk_pos);
			$body .= substr($str, 0, $chunk_size);
		}
		return $body;
	}
	
	/**
	 * 检查CURL请求是否返回了成功（200）
	 */
	public function is_OK(){
		return ($this->code >= 200 && $this->code <= 299) ? TRUE : FALSE;
	}
	

}