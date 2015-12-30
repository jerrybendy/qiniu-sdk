<?php
if ( ! defined('QINIU_INCLUDE_PATH')) exit('No direct script access allowed');
/**
 * 七牛云存储文件管理模块
 * 
 * @author       Jerry
 * @link            http://blog.icewingcc.com
 * @package    Qiniu
 * @since          Version 1.1
 * 
 * 最后更新：2014-08-27
 * 【新增】ls函数，用于列举文件
 * 【新增】ls_resume函数，用于分页的列举
 */

class Qiniu_RS_Exception extends Exception{
	
}

class Qiniu_rs extends Qiniu{
	/**
	 * RS服务器地址
	 */
	private $_rs_host = 'http://rs.qiniu.com';
	
	public function __construct($config = array()){
		parent::__construct($config);
	}
	
	
	/**
	 * 获取资源的Metadata信息
     * @since v1.0
	 * @param string|array $filename 要获取信息的文件名，字符串（文件名）或数组（Bucket和文件名）
	 * @param array $opt 可选参数：
	 * @return Qiniu_request 返回一个Request对象
	 */
	public function stat($filename, $opt = array()){
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
     * @since v1.0
	 * @param string|array $src_file 要移动（重命名）的源文件，要求一个字符串或数组格式的资源文件名
	 * @param string|array $dest_file 目标文件名
	 * @param array $opt 可选的部分参数
	 * @return Qiniu_response
	 */
	public function move($src_file, $dest_file, $opt = array()){
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
     * @since v1.0
	 * @see Qiniu_rs::move
	 * @return Qiniu_response
	 */
	public function rename($src_file, $dest_file, $opt=array()){
		return $this->move($src_file, $dest_file, $opt);
	}
	
	
	/**
	 * 复制一个资源文件
     * @since v1.0
	 * @param string|array $src_file 要复制的源文件，要求一个字符串或数组格式的资源文件名
	 * @param string|array $dest_file 目标文件名
	 * @param array $opt 可选的部分参数
	 * @return Qiniu_response
	 */
	public function copy($src_file, $dest_file, $opt = array()){
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
     * @since v1.0
	 * @param string|array $filename 要获取信息的文件名，字符串（文件名）或数组（Bucket和文件名）
	 * @param array $opt 可选参数：
	 * @return Qiniu_request 返回一个Request对象
	 */
	public function delete($filename, $opt = array()){
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
     * @since v1.0
	 * @param string $url 要抓取的文件 的URL
	 * @param string|array $filename 要保存到的Bucket和文件名
	 * @return Qiniu_response
	 */
	public function fetch($url, $filename){
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
     * @since v1.0
	 * @param string|array $filename 要更新的资源文件名
	 * @return Qiniu_response
	 */
	public function prefetch($filename){
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
	 * 在一次成功执行ls列举文件后判断是否有下一页可供查询，
	 * 如果有的话将临时把下一次的调用参数保存在这里
	 * @var array
	 */
	private $_ls_resume_params = array();
	
	/**
	 * 列举资源
	 * 本接口用于将七牛中指定的文件分批列出
	 *
     * @since v1.0
	 * @param string $prefix 指定前缀，只有资源名匹配该前缀的资源会被列出。缺省值为空字符串。
	 * @param int $limit 【可选】列举的条目数，范围1-1000，默认为1000
	 * @param string $delimiter 【可选】指定目录分隔符，列出所有公共前缀（模拟列出目录效果），默认为空
	 * @param string $bucket 【可选】指定列举文件的空间，默认为预设的空间
	 * @param string $marker 【可选】上一次列举返回的位置标记，作为本次列举的起点信息
	 * @return Qiniu_response
	 */
	public function ls($prefix = '', $limit = NULL, $delimiter = NULL, $bucket = NULL, $marker = NULL){
		$_ls_host = 'http://rsf.qbox.me'; //列举文件服务器地址
		
		$query = array();
		$this->_ls_resume_params = array();
		
		$query ['bucket'] = ($bucket == NULL) ? $this->_bucket : $bucket;
		$query ['prefix'] = $prefix;
		if($limit != NULL)
			$query ['limit'] = $limit;
		if($delimiter != NULL)
			$query ['delimiter'] = $delimiter;
		if($marker != NULL)
			$query ['marker'] = $marker;
		
		$uri = $_ls_host . '/list?' . http_build_query($query);
		$token = $this->auth->sign_request($uri);
		
		$req = new Qiniu_request($uri);
		$req->set_header('Authorization',  'QBox ' . $token);
		
		$resp = $req->make_request();
		
		//检查是否有分页
		if($resp->is_OK()){
			if(isset($resp->data['marker'])){
				$this->_ls_resume_params = array($prefix, $limit, $delimiter, $bucket, $resp->data['marker']);
			}
		}
		
		return $resp;
	}

	/**
	 * 继续上一次的列举文件操作
	 * 使用与上一次ls列举文件相同的参数获取下一页数据，并在没有下一页数据时返回 FALSE
     * @since v1.0
	 * @return FALSE|Qiniu_response
	 */
	public function ls_resume(){
		if(empty($this->_ls_resume_params))
			return FALSE;
		return call_user_func_array(array($this, 'ls'), $this->_ls_resume_params);
	}


    /**
     * 修改文件的元信息(MIME类型)
     *
     * @see http://developer.qiniu.com/docs/v6/api/reference/rs/chgm.html
     * @since v1.0
     * @param $filename
     * @param $mime_type
     * @return Qiniu_response
     * @throws Qiniu_Exception
     */
	public function change_meta($filename, $mime_type){
		$file = $this->_filename_to_array($filename);

		$uri = $this->_rs_host . '/chgm/' . $this->auth->url_safe_base64_encode("{$file['bucket']}:{$file['key']}");
		$uri = $uri . '/mime/' . $this->auth->url_safe_base64_encode($mime_type);
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
     * @since v1.0
	 * @return Qiniu_rs
	 */
	public function batch_add_stat($filename){
		$this->_batch_ops [] = array(
				'op' => 'stat',
				'param' => array( $filename)
				);
 		return $this;
	}
	
	/**
	 * 添加一个移动/重命名操作到队列
     * @since v1.0
	 * @return Qiniu_rs
	 */
	public function batch_add_move($src_file, $dest_file){
		$this->_batch_ops [] = array(
				'op' => 'move',
				'param' => array($src_file, $dest_file)
				);
 		return $this;
	}
	
	/**
	 * 添加一个复制文件操作到队列
     * @since v1.0
	 * @return Qiniu_rs
	 */
	public function batch_add_copy($src_file, $dest_file){
		$this->_batch_ops [] = array(
				'op' => 'copy',
				'param' => array($src_file, $dest_file)
		);
 		return $this;
	}
	
	/**
	 * 添加一个删除操作到队列
     * @since v1.0
	 * @return Qiniu_rs
	 */
	public function batch_add_delete($filename){
		$this->_batch_ops [] = array(
				'op' => 'delete',
				'param' => array( $filename)
		);
 		return $this;
	}
	
	/**
	 * 清空队列中的操作
     * @since v1.0
	 * @return Qiniu_rs
	 */
	public function batch_clear(){
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
     * @since v1.0
	 * @return Qiniu_response
	 * @throws Qiniu_RS_Exception
	 */
	public function do_batch($ops = array()){
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