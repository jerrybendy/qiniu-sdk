<?php
if ( ! defined('QINIU_INCLUDE_PATH')) exit('No direct script access allowed');
/**
 * 七牛云存储文件管理模块
 * 
 * @author       Jerry
 * @link            http://blog.icewingcc.com
 * @package    Qiniu
 * @since          Version 1.0
 * 
 * 最后更新：2014-4-9
 */

class Qiniu_RS_Exception extends Exception{
	
}

class Qiniu_rs extends Qiniu{
	/**
	 * RS服务器地址
	 */
	private $_rs_host = 'http://rs.qiniu.com';
	
	function __construct($config = array()){
		parent::__construct($config);
		
	}
	
	
	/**
	 * 获取资源的Metadata信息
	 * @param string|array $filename 要获取信息的文件名，字符串（文件名）或数组（Bucket和文件名）
	 * @param $opt 可选参数：
	 * @return Qiniu_request 返回一个Request对象
	 */
	function stat($filename, $opt = array()){
		$file = $this->_filename_to_array($filename);
		
		$uri = $this->_rs_host . '/stat/' . $this->auth->url_safe_base64_encode("{$file['bucket']}:{$file['key']}");
		$token = $this->auth->sign_request($uri);

		$req = new Qiniu_request($uri);
		$req->set_header('Authorization',  'QBox ' . $token);

		$resp = $req->make_request();
		return $resp;
	}

	/**
	 * 移动（重命名）一个资源文件
	 * @param string|array $src_file 要移动（重命名）的源文件，要求一个字符串或数组格式的资源文件名
	 * @param string|array $dest_file 目标文件名
	 * @param $opt 可选的部分参数
	 * @return Qiniu_response
	 */
	function move($src_file, $dest_file, $opt = array()){
		$src = $this->_filename_to_array($src_file);
		$dest = $this->_filename_to_array($dest_file);
		
		$uri = $this->_rs_host . '/move/' . $this->auth->url_safe_base64_encode("{$src['bucket']}:{$src['key']}") . '/';
		$uri .= $this->auth->url_safe_base64_encode("{$dest['bucket']}:{$dest['key']}");
		$token = $this->auth->sign_request($uri);
		
		$req = new Qiniu_request($uri);
		$req->set_header('Authorization',  'QBox ' . $token);
		
		$resp = $req->make_request();
		return $resp;
	}
	
	/**
	 * 重命名一个文件
	* @see Qiniu_rs::move
	 * @return Qiniu_response
	 */
	function rename($src_file, $dest_file, $opt=array()){
		return $this->move($src_file, $dest_file, $opt);
	}
	
	
	/**
	 * 复制一个资源文件
	 * @param string|array $src_file 要复制的源文件，要求一个字符串或数组格式的资源文件名
	 * @param string|array $dest_file 目标文件名
	 * @param $opt 可选的部分参数
	 * @return Qiniu_response
	 */
	function copy($src_file, $dest_file, $opt = array()){
		$src = $this->_filename_to_array($src_file);
		$dest = $this->_filename_to_array($dest_file);
	
		$uri = $this->_rs_host . '/copy/' . $this->auth->url_safe_base64_encode("{$src['bucket']}:{$src['key']}") . '/';
		$uri .= $this->auth->url_safe_base64_encode("{$dest['bucket']}:{$dest['key']}");
		$token = $this->auth->sign_request($uri);
	
		$req = new Qiniu_request($uri);
		$req->set_header('Authorization',  'QBox ' . $token);
	
		$resp = $req->make_request();
		return $resp;
	}
	
	
	/**
	 * 删除一个资源文件
	 * @param string|array $filename 要获取信息的文件名，字符串（文件名）或数组（Bucket和文件名）
	 * @param $opt 可选参数：
	 * @return Qiniu_request 返回一个Request对象
	 */
	function delete($filename, $opt = array()){
		$file = $this->_filename_to_array($filename);
	
		$uri = $this->_rs_host . '/delete/' . $this->auth->url_safe_base64_encode("{$file['bucket']}:{$file['key']}");
		$token = $this->auth->sign_request($uri);
	
		$req = new Qiniu_request($uri);
		$req->set_header('Authorization',  'QBox ' . $token);
	
		$resp = $req->make_request();
		return $resp;
	}
	
	
	/**
	 * 抓取一个网络文件并存储到云空间中
	 * @param string $url 要抓取的文件 的URL
	 * @param string|array $filename 要保存到的Bucket和文件名
	 * @return Qiniu_response
	 */
	function fetch($url, $filename){
		$fetch_host = 'http://iovip.qbox.me';
		$file = $this->_filename_to_array($filename);
		
		$uri = $fetch_host . '/fetch/' . $this->auth->url_safe_base64_encode($url);
		$uri = $uri . '/to/' . $this->auth->url_safe_base64_encode("{$file['bucket']}:{$file['key']}");
		$token = $this->auth->sign_request($uri);
		
		$req = new Qiniu_request($uri);
		$req->set_header('Authorization',  'QBox ' . $token);
	
		$resp = $req->make_request();
		return $resp;
	}
	
	
	/**
	 * 更新镜像资源
	 * @param string|array $filename 要更新的资源文件名
	 * @return Qiniu_response
	 */
	function prefetch($filename){
		$fetch_host = 'http://iovip.qbox.me';
		$file = $this->_filename_to_array($filename);
	
		$uri = $fetch_host . '/prefetch/'. $this->auth->url_safe_base64_encode("{$file['bucket']}:{$file['key']}");
		$token = $this->auth->sign_request($uri);
	
		$req = new Qiniu_request($uri);
		$req->set_header('Authorization',  'QBox ' . $token);
	
		$resp = $req->make_request();
		return $resp;
	}
	
	/**
	 * **************************************************************************
	 * 批量操作部分：
	 * 
	 * 可以使用 batch_add_xxx的方式添加一个操作到批量操作队列中，函数的参数与单独使用时相同
	 * 批量操作的添加方式支持链式操作，如
	 *   $qiniu->rs->batch_add_stat('abc.jpg')
	 *                   ->batch_add_move('abc.jpg', 'ccc.jpg')
	 *                   ->batch_add_copy('ccc.jpg', 'ddd.jpg')
	 *                   ->batch_add_delete('ccc.jpg')
	 *                   ->do_batch();
	 * 这样可以很方便地在一条语句中组织多种不同的批量操作。
	 * 
	 * 所有 batch_add_xxx函数均返回  $this （这对使用这个库来说没有任何意义，仅用于链式操作需要）
	 * do_batch返回一个QiniuResponse对象，Body包含一个二维数组（JSON格式），数组的每一个项
	 * 分别对应每一个添加的操作
	 * 
	 */
	
	/**
	 * 存储所有已经添加的批量操作
	 */
	private $_batch_ops = array();
	
	/**
	 * 添加一个获取文件元数据的操作到批量队列
	 * @return Qiniu_rs
	 */
	function batch_add_stat($filename){
		$this->_batch_ops [] = array(
				'op' => 'stat',
				'param' => array( $filename)
				);
 		return $this;
	}
	
	/**
	 * 添加一个移动/重命名操作到队列
	 * @return Qiniu_rs
	 */
	function batch_add_move($src_file, $dest_file){
		$this->_batch_ops [] = array(
				'op' => 'move',
				'param' => array($src_file, $dest_file)
				);
 		return $this;
	}
	
	/**
	 * 添加一个复制文件操作到队列
	 * @return Qiniu_rs
	 */
	function batch_add_copy($src_file, $dest_file){
		$this->_batch_ops [] = array(
				'op' => 'copy',
				'param' => array($src_file, $dest_file)
		);
 		return $this;
	}
	
	/**
	 * 添加一个删除操作到队列
	 * @return Qiniu_rs
	 */
	function batch_add_delete($filename){
		$this->_batch_ops [] = array(
				'op' => 'delete',
				'param' => array( $filename)
		);
 		return $this;
	}
	
	/**
	 * 清空队列中的操作
	 * @return Qiniu_rs
	 */
	function batch_clear(){
		$this->_batch_ops = array();
		return $this;
	}
	
	/**
	 * 执行一次批量操作
	 * ！！！注意：如果参数中设置了$ops数组，将自动忽略并清空所有通过 batch_add_*添加的操作序列
	 * @param array $ops 需要执行的操作的组合，需要符合以下格式：
	 *      $ops = array(
	 *      	[0] => array (
	 *      			'op' => 'xxxx',
	 *      			'param' => array (xxxx)
	 *      		),
	 *      	[1] => array (......
	 *      ....
	 *      ) )
	 *      
	 */
	function do_batch($ops = array()){
		if(!empty($ops)){
			$this->_batch_ops = $ops;
		}

		$op_arr = array();
		foreach ($this->_batch_ops as $op){
			if(isset($op['op']) && isset($op['param']) && is_array($op['param'])){
				switch($op['op']){
					case 'stat':
					case 'delete':
						try{
							$file = $this->_filename_to_array($op['param'][0]);
							$op_arr [] = "/{$op['op']}/" . $this->auth->url_safe_base64_encode("{$file['bucket']}:{$file['key']}");
						} catch (Exception $e){
							throw new Qiniu_RS_Exception('Invalid Params In Doing Batch Request');
						}
						break;
					case 'copy':
					case 'move':
						try{
							$src = $this->_filename_to_array($op['param'][0]);
							$dest = $this->_filename_to_array($op['param'][1]);
							$uri = "/{$op['op']}/" . $this->auth->url_safe_base64_encode("{$src['bucket']}:{$src['key']}") . '/';
							$uri .= $this->auth->url_safe_base64_encode("{$dest['bucket']}:{$dest['key']}");
							$op_arr [] = $uri;
						} catch (Exception $e){
							throw new Qiniu_RS_Exception('Invalid Params In Doing Batch Request');
						}
						break;
					default:
						continue;
				}
			} else {
				throw new Qiniu_RS_Exception('Invalid Params In Doing Batch Request');
			}
		}
		
		$body = 'op=' . implode('&op=', $op_arr);
		
		$req = new Qiniu_request($this->_rs_host . '/batch', $body);
		$req->set_header('Content-Type', 'application/x-www-form-urlencoded');
		
		$token = $this->auth->sign_request($this->_rs_host . '/batch', $body);
		$req->set_header('Authorization',  'QBox ' . $token);
		
		$resp = $req->make_request();
		return $resp;
	}
}