<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
* 七牛云存储缩略图生成模块
* @author Jerry Bendy
* @date 2014-7-8
*/

class Qiniu_Image_Exception extends Exception{
	
}

class Qiniu_image extends Qiniu{
	
	function __construct($config = array()){
		parent::__construct($config);
	
	}
	

	/**
	 * 创建一个图像的缩略图
	 * @param $filename 要创建缩略图的文件名
	 * @param array $opt 创建时的选项，要求一个以下格式的数组：
	 *           array('mode'=>1, 'width'=>200, 'height' =>200, 'q'=>85, 'format'=>'png', 'interlace' =>0)
	 * @return string
	 */
	function view($filename, array $opt){
		$opt_default = array('mode'   => 1);
		$opt = $opt + $opt_default;
		
		$query = 'imageView2/' . $opt['mode'];
		if(isset($opt['width']))
			$query .= '/w/' . $opt['width'];
		if(isset($opt['height']))
			$query .= '/h/' . $opt['height'];
		if(isset($opt['q']))
			$query .= '/q/' . $opt['q'];
		if(isset($opt['format']))
			$query .= '/format/' . $opt['format'];
		if(isset($opt['interlace']))
			$query .= '/interlace/' . $opt['interlace'];
		
		$url = $this->dl->get_url_public($filename) . '?' . $query;
		
		if($this->_auth == 'private'){
			$token = $this->auth->sign($url);
			return "{$url}&token={$token}";
		}
		
		return $url;
	}
	
	
	/**
	 * 获取图片信息
	 * 如果命令执行成功会返回一个包含图片信息的数组，详见七牛官方文档
	 * 如果执行失败会返回一个包含错误码和错误描述的数组或者FALSE，所以
	 * 需要预先判断返回的数组是否正确
	 * @param $filename 文件名
	 * @return mixed|boolean
	 */
	function info($filename){
		$url = $this->dl->get_url_public($filename) . '?imageInfo' ;
		
		if($this->_auth == 'private'){
			$token = $this->auth->sign($url);
			$url = "{$url}&token={$token}";
		}
		
		$ret = @file_get_contents($url);
		if($ret){
			$con = json_decode($ret, true);
			return $con;
		} else {
			return FALSE;
		}
		
	}
	
	
	/**
	 * 七牛图像高级操作，详细参数见七牛官方文档
	 * @link http://developer.qiniu.com/docs/v6/api/reference/fop/image/imagemogr2.html
	 * @param $filename 文件名
	 * @param array $opt 参数数组，详见文档（没有值的参数可以将数组值设为空，如 'strip'=>''
	 * @return string
	 */
	function mogr($filename, array $opt){
		$query = 'imageMogr2/';
		
		foreach ($opt as $key=>$val){
			$query .= "/{$key}" . (empty($val)? '' : "/{$val}");
		}
		
		$url = $this->dl->get_url_public($filename) . '?' . $query;
		
		if($this->_auth == 'private'){
			$token = $this->auth->sign($url);
			return "{$url}&token={$token}";
		}
		
		return $url;
	}
	
}