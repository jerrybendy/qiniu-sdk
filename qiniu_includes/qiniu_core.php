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
		
		$resp = new Qiniu_response();	
		
// 		$options[CURLOPT_HEADERFUNCTION] = array($this, '_header_func');
		
		curl_setopt_array($ch, $options);
		$result = curl_exec($ch);
		$ret = curl_errno($ch);
		if ($ret !== 0) {
			throw new Qiniu_Exception('An CURL error has occured, ' . curl_error($ch));
		}
		
		$resp->Body = $result;
		$resp->StatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$resp->Header['Content-Type'] = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
		curl_close($ch);

		return $resp;
// return $this;
		
	}
	
	
}

/**
 * HTTP响应相关类
 */
class Qiniu_response{
	/**
	 * 返回的HTTP响应代码
	 */
	public $StatusCode;
	
	/**
	 * 返回的HTTP头，数组
	 */
	public $Header;
	
	/**
	 * 返回内容字节数
	 */
	public $ContentLength;
	
	/**
	 * 返回的内容主体，
	 * 如果内容为JSON格式将会被转换为数组的形式
	 */
	public $Body;
	


	public function __construct($code = 200, $body = '')
	{
		$this->StatusCode = $code;
		$this->Header = array();
		$this->ContentLength = strlen($body);
		
		//将传入的JSON格式的主体转换为数组形式
		$arr = json_decode($body, TRUE);
		if($arr == NULL){
			$this->Body = $body;
		} else {
			$this->Body = $arr;
		}
	}
	
	/**
	 * 检查CURL请求是否返回了成功（200）
	 */
	public function is_OK(){
		return ($this->StatusCode >= 200 && $this->StatusCode <= 299) ? TRUE : FALSE;
	}
	
	/**
	 * 获取错误描述信息
	 */
	public function err_msg(){
		return isset($this->Body['error']) ? $this->Body['error'] : '';
	}
	
	/**
	 * 获取错误代码
	 */
	public function err_code(){
		return isset($this->Body['code']) ? $this->Body['code'] : '';
	}
}