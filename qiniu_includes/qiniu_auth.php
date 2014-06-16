<?php
if ( ! defined('QINIU_INCLUDE_PATH')) exit('No direct script access allowed');
/**
 * 七牛云存储授权/密钥/签名操作模块
 *
 * @author       Jerry
 * @link            http://blog.icewingcc.com
 * @package    Qiniu
 * @since          Version 1.0
 *
 * 最后更新：2014-4-7
 */

class Qiniu_auth extends Qiniu{
	
	function __construct($config = array()){
		parent::__construct($config);
	}
	
	/**
	 * 获取一个hmac_sha1签名字符串，并将其与AK连接
	 * @param unknown_type $data
	 */
	function sign($data){
		$sign = hash_hmac('sha1', $data, $this->_sk, true);
		return $this->_ak . ':' . $this->url_safe_base64_encode($sign);
	}
	
	/**
	 * 
	 * @param unknown_type $url
	 * @param unknown_type $incbody
	 */
	public function sign_request($url, $incbody = ''){
		$url = parse_url($url);
		$data = '';
		if (isset($url['path'])) {
			$data = $url['path'];
		}
		if (isset($url['query'])) {
			$data .= '?' . $url['query'];
		}
		$data .= "\n";
	
		if ($incbody) {
			$data .= $incbody;
		}
		return $this->Sign($data);
	}
	
	/**
	 * 为一个包含数据的字符串签名（上传策略）
	 * @param $data
	 */
	public function sign_with_data($data){
		$data = $this->url_safe_base64_encode($data);
		return $this->sign($data) . ':' . $data;
	}
	
	
	/**
	 * 对字符串进行URL安全的Base64编码
	 * @param unknown_type $str
	 * @return mixed
	 */
	function url_safe_base64_encode($str){
		$find = array('+', '/');
		$replace = array('-', '_');
		return str_replace($find, $replace, base64_encode($str));
	}
	
	/**
	 * 对URL安全的Base64编码字符串进行解码
	 * @param unknown_type $str
	 * @return string
	 */
	function url_safe_base64_decode($str)
	{
		$find = array('-', '_');
		$replace = array('+', '/');
		return base64_decode(str_replace($find, $replace, $str));
	}
	
	/**
	 * 对一个字符串进行hmac_sha1签名，并返回签名后的字符串
	 * @param $str 源字符串
	 * @return 返回签名后的字符串
	 */
	function hmac_sha1($str){
		$sign = hash_hmac('sha1', $str, $this->_sk, true);
		return $this->url_safe_base64_encode($sign);
	}
}