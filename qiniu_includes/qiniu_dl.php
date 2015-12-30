<?php
if ( ! defined('QINIU_INCLUDE_PATH')) exit('No direct script access allowed');
/**
 * 七牛文件下载地址生成部分模块
 *
 * @author       Jerry
 * @link            http://blog.icewingcc.com
 * @package    Qiniu
 * @since          Version 1.1.2
 *
 * 最后更新：2015-5-8
 * 2015-5-8
 * -- 添加获取文件内容的函数
 * 
 * 2014-7-6
 * -- 修改函数的访问形式以及自定义域名的使用
 */

class Qiniu_dl extends Qiniu{
	
	public function __construct($config = array()){
		parent::__construct($config);
	}
	
	#----------------------------------------------------------------
	/**
	 * 获取公开资源的访问URL
	 * @since v1.0
	 * @param string|array $filename 资源名称
	 * @param bool $download 是否是创建一个下载资源的URL
	 * @param string $dl_filename 仅在$download为TRUE时，下载的文件名
	 * @return string 返回生成的访问或下载URL
	 */
	public function get_url_public($filename, $download = FALSE, $dl_filename = NULL, $expried = 7200){
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
	
	#----------------------------------------------------------------
	/**
	 * 获取私有资源的访问或下载链接
	 * @since v1.0
	 * @param string|array $filename 资源名称
	 * @param bool $download 是否是创建一个下载资源的URL
	 * @param string $dl_filename  仅在$download为TRUE时，下载的文件名
	 * @param int $expried 链接的有效时间，默认是2小时
	 * @return string 返回生成的访问或下载的URL
	 */
	public function get_url_private($filename, $download = FALSE, $dl_filename = NULL, $expried = 7200){
		$url = $this->get_url_public($filename, $download, $dl_filename);
		$url .= (strpos($url, '?') ? '&' : '?') . 'e=' . (time() + $expried);
		
		$token = $this->auth->sign($url);
		return "{$url}&token={$token}";
	}

	#----------------------------------------------------------------
	/**
	 * 返回/保存指定文件的内容
	 * @since v1.0
	 * @param string|array $filename 资源名称
	 * @param string|bool $saveas   指定要保存获取到文件的路径，为FALSE时不保存文件
	 * @return string|bool   成功返回内容，失败返回FALSE（设置了saveas参数会在保存失败时返回FALSE）
	 */
	public function get_content($filename, $saveas = FALSE){
		$content = @file_get_contents($this->get_url($filename, FALSE, NULL, 600));
		if($content){
			if($saveas){
				$ret = @file_put_contents($saveas, $content);
				if($ret === FALSE){
					return FALSE;
				}
			}
			return $content;
		} else {
			return FALSE;
		}
	}
	
}