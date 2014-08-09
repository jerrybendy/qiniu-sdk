<?php
if ( ! defined('QINIU_INCLUDE_PATH')) exit('No direct script access allowed');
/**
 * 七牛文件下载地址生成部分模块
 *
 * @author       Jerry
 * @link            http://blog.icewingcc.com
 * @package    Qiniu
 * @since          Version 1.0
 *
 * 最后更新：2014-7-6
 * 
 * 2014-7-6
 * -- 修改函数的访问形式以及自定义域名的使用
 */

class Qiniu_dl extends Qiniu{
	
	function __construct($config = array()){
		parent::__construct($config);
	}
	
	/**
	 * 获取公开资源的访问URL
	 * @param $filename 资源名称
	 * @param $download 是否是创建一个下载资源的URL
	 * @param $dl_filename 仅在$download为TRUE时，下载的文件名
	 * @return string 返回生成的访问或下载URL
	 */
	function get_url_public($filename, $download = FALSE, $dl_filename = NULL){
		$url = '';
		$file = $this->_filename_to_array($filename);
		if($this->_domain){
			$url = $this->_domain . $file['key'];
		} else {
			$url = "http://{$file['bucket']}.qiniudn.com/{$file['key']}";
		}
		
		if($download){
			$url .= '?download';
			if($dl_filename)
				$url .= '/' . $dl_filename;
		}
		
		return $url;
	}
	
	/**
	 * 获取私有资源的访问或下载链接
	 * @param $filename 资源名称
	 * @param $download 是否是创建一个下载资源的URL
	 * @param $dl_filename  仅在$download为TRUE时，下载的文件名
	 * @param $expried 链接的有效时间，默认是2小时
	 * @return string 返回生成的访问或下载的URL
	 */
	function get_url_private($filename, $download = FALSE, $dl_filename = NULL, $expried = 7200){
		$url = $this->get_url_public($filename, $download, $dl_filename);
		$url .= (strpos($url, '?') ? '&' : '?') . 'e=' . (time() + $expried);
		
		$token = $this->auth->sign($url);
		return "{$url}&token={$token}";
	}
	
	
	
	
	
	
	/**
	 * 获取公开资源的访问URL
	 * @param $filename 资源的名称
	 * @param $opt 可选的参数数组，支持以下参数
	 * 					download   TRUE|FALSE，默认为FALSE，设置为TRUE时将返回一个下载地址
	 * 					fname			string 自定义下载文件的文件名，如设置此参数则默认download为TRUE
	 * @return string 返回一个经过处理的URL
	 
	function get_public($filename, $opt = array()){
		$url = '';
		$file = $this->_filename_to_array($filename);
		if($this->_domain){
			$url = $this->_domain . $file['key'];
		} else {
			$url = "http://{$file['bucket']}.qiniudn.com/{$file['key']}";
		}

		if(isset($opt['fname']) && !empty($opt['fname'])){
			$url .= "?download/{$opt['fname']}";
		} elseif (isset($opt['download']) && $opt['download'] == TRUE){
			$url .= '?download';
		}

		return $url;
	}

	
	/**
	 * 获取一个私有资源的访问URL
	 * @param $filename 资源名称
	 * @param $expired 资源到期时间，默认2小时，即7200秒
	 * @param array $opt 可选参数数组
	 * @see Qiniu_download::get_public()
	 * @return 返回访问资源的URL

	function get_private($filename, $expired = 7200, $opt = array()){
		$file = $this->_filename_to_array($filename);
		
		$url = "http://{$file['bucket']}.qiniudn.com/{$file['key']}";

		$query = 'e=' .  (time() + $expired);
		
		if(isset($opt['fname']) && !empty($opt['fname'])){
			$query .= "&download/{$opt['fname']}";
		} elseif (isset($opt['download']) && $opt['download'] == TRUE){
			$query .= '&download';
		}		
		
		$url = $url . '?' . $query;
		
		$token = $this->auth->sign($url);
		
		return "{$url}&token={$token}";
	}
	*/
}