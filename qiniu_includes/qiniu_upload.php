<?php
if ( ! defined('QINIU_INCLUDE_PATH')) exit('No direct script access allowed');
/**
 * 上传部分
 * 
 * @author       Jerry
 * @link            http://blog.icewingcc.com
 * @package    Qiniu
 * @version      Version 1.1
 *
 * 最后更新：2014-8-26
 * 【新增】字符串上传函数
 * 【新增】简单上传允许使用上传策略控制上传的细节
 * 【修正】修复简单上传中可能出现的建立表单失败的问题
 */

class Qiniu_upload extends Qiniu{
	private $_up_host = 'http://up.qiniu.com/';
	
	
	function __construct($config = array()){
		parent::__construct($config);
		
		//初始化上传策略类
		$this->put_policy->init();
	}
	
	/**
	 * 上传一个文件到云存储
	 * @since v1.0
	 * @param string $filetoupload 要上传的文件（文件路径）
	 * @param string $saveKey 要存储为的文件名（如果让七牛设置可将此参数置为空）
	 * @param string $saveBucket 要上传到的空间名（为空时表示使用默认的Bucket）
	 * @param bool $use_custom_put_policy 是否使用自定义的上传策略
	 *                        设置这个参数为TRUE时需要使用Qiniu_put_policy类设置上传策略，函数将会自动调用
	 * @param int $expired 上传执行的有效期
	 * @return string|Qiniu_response
	 * @throws Qiniu_Exception
	 */
	function upload($filetoupload, $saveKey, $saveBucket = '', $use_custom_put_policy = FALSE, $expired = 7200){
		if(file_exists($filetoupload) && is_file($filetoupload)){
			$cont = @file_get_contents($filetoupload);
			if($cont === false)
				throw new Qiniu_Exception('UploadFailed: File cannot read');
			
			return $this->upload_string($cont, $saveKey, $saveBucket, $use_custom_put_policy, $expired);
		} else {
			throw new Qiniu_Exception('UploadFailed: File is not exists');
		}
		
	}
	
	/**
	 * 上传一个字符串到七牛，并存储为指定的文件名
	 * @since v1.0
	 * @param string $filetoupload 要上传的文件（文件路径）
	 * @param string $saveKey 要存储为的文件名（如果让七牛设置可将此参数置为空）
	 * @param string $saveBucket 要上传到的空间名（为空时表示使用默认的Bucket）
	 * @param bool $use_custom_put_policy 是否使用自定义的上传策略
	 *                        设置这个参数为TRUE时需要使用Qiniu_put_policy类设置上传策略，函数将会自动调用
	 * @param int $expired 上传执行的有效期
	 * @return string|Qiniu_response
	 */
	function upload_string($strtoupload, $saveKey, $saveBucket = '', $use_custom_put_policy = FALSE, $expired = 7200){
		$pp = $this->put_policy;
		
		if( ! $use_custom_put_policy){
			$pp->clear_policy();
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
				
			//设置上传策略
			$pp->set_policy_array($policy);
		}
		
		//获取上传Token
		$token = $pp->get_token();
		
		$param['token'] = $token;
		$param['key'] = $saveKey;
		
		$the_file['files']['file'] = array($saveKey, $strtoupload);
		
		//
		list($boundary, $body) = $this->buildMultiForm($param, $the_file['files']);
		
		$req = new Qiniu_request($this->_up_host, $body);
		$req->set_header('Content-Type', 'multipart/form-data; boundary=' . $boundary);
		
		$resp = $req->make_request();
		
		return $resp;
	}
	
	/**
	 * Parse multi form
	 *
	 * @since v1.0
	 * @param array $form
	 * @param       $files
	 * @throws \InvalidArgumentException
	 * @return array
	 */
	private function buildMultiForm($form, $files)
	{
		$data = array();
		$boundary = md5(uniqid());
	
		foreach ($form as $name => $val) {
			$data[] = '--' . $boundary;
			$data[] = "Content-Disposition: form-data; name=\"$name\"";
			$data[] = '';
			$data[] = $val;
		}
	
		foreach ($files as $name => $file) {
			$data[] = '--' . $boundary;
			$filename = str_replace(array("\\", "\""), array("\\\\", "\\\""), $file[0]);
			$data[] = "Content-Disposition: form-data; name=\"$name\"; filename=\"$filename\"";
			$data[] = 'Content-Type: application/octet-stream';
			$data[] = '';
			$data[] = $file[1];
		}
	
		$data[] = '--' . $boundary . '--';
		$data[] = '';
	
		$body = implode("\r\n", $data);
		return array($boundary, $body);
	}
}