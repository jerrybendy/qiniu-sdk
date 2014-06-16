<?php
if ( ! defined('QINIU_INCLUDE_PATH')) exit('No direct script access allowed');

class Qiniu_upload extends Qiniu{
	private $_up_host = 'http://up.qiniu.com/';
	
	
	function __construct($config = array()){
		parent::__construct($config);
	}
	
	/**
	 * 上传一个文件到云存储
	 * @param string $filetoupload 要上传的文件（文件路径）
	 * @param string $saveKey 要存储为的文件名（如果让七牛设置可将此参数置为空）
	 * @param string $saveBucket 要上传到的空间名（为空时表示使用默认的Bucket）
	 * @param int $expired 上传执行的有效期
	 * @return string|Qiniu_response
	 */
	function upload($filetoupload, $saveKey, $saveBucket = '', $expired = 7200){
		$pp = $this->_load_class('put_policy');
		$pp->clear_policy();
// 		$pp = new Qiniu_put_policy();  //临时的代码
		
		$policy = array();
		
		//判断传入的savename是一个Bucket名还是一个Bucket:key的数组
		if ( empty($saveBucket))
			$saveBucket = $this->_bucket;
		
		if( empty($saveKey) ){
			$policy [Qiniu_put_policy::QINIU_PP_SCOPE] = $saveBucket;
		} else {
			$policy [Qiniu_put_policy::QINIU_PP_SCOPE] = $saveBucket . ':' . $saveKey;
		}
		
		$policy [Qiniu_put_policy::QINIU_PP_DEADLINE] = time() + $expired;

		//设置上传策略并获取Token
		$pp->set_policy_array($policy);
		
		$token = $pp->get_token();

		$param['token'] = $token;
		$param['key'] = $saveKey;
		$param['file'] = '@' . $filetoupload;
		
		
		$req = new Qiniu_request($this->_up_host, $param);
		$req->set_header('Content-Type', 'multipart/form-data');
		
		$resp = $req->make_request();

		return $resp;
		
	}
	

}