<?php
if ( ! defined('QINIU_INCLUDE_PATH')) exit('No direct script access allowed');
/**
 * 七牛上传策略类
 * @author Jerry
 *
 */
class Qiniu_put_policy extends Qiniu{
	
	//对各种上传策略类型的定义
	
	/**
	 * 指定上传的Bucket和Key，可以使用<Bucket>:<Key>的形式或<Bucket>的形式
	 */
	const QINIU_PP_SCOPE = 'scope';
	
	/**
	 * 上传请求授权的截止时间，Unix时间戳，单位秒
	 */
	const QINIU_PP_DEADLINE = 'deadline';
	
	/**
	 * 限定为“新增”语意，如果设置为非0值，则无论scope设置为什么形式，仅能以新增模式上传文件
	 */
	const QINIU_PP_INSERT_ONLY = 'insertOnly';
	
	/**
	 * 唯一属主标识，特殊场景下非常有用，比如根据App-Client标识给图片或视频打水印。
	 */
	const QINIU_PP_END_USER = 'endUser';
	
	/**
	 * 上传成功后执行303跳转的URL，
	 * 文件上传成功后会跳转到<returnUrl>?upload_ret=<queryString>, 
	 * <queryString>包含returnBody内容。
	 * 如不设置returnUrl，则直接将returnBody的内容返回给客户端。
	 */
	const QINIU_PP_RETURN_URL = 'returnUrl';
	
	/**
	 * 上传成功后，自定义七牛云最终返回給上传端（在指定returnUrl时是携带在跳转路径参数中）的数据
	 * 支持魔法变量和自定义变量。
	 * returnBody 要求是合法的 JSON 文本。
	 * 如：{"key": $(key), "hash": $(etag), "w": $(imageInfo.width), "h": $(imageInfo.height)}。
	 */
	const QINIU_PP_RETURN_BODY = 'returnBody';
	
	/**
	 * 上传成功后，七牛云向App-Server发送POST请求的URL
	 * 必须是公网上可以正常进行POST请求并能响应HTTP/1.1 200 OK的有效URL。
	 * 另外，为了给客户端有一致的体验，我们要求 callbackUrl 返回包 Content-Type 为 "application/json"，
	 * 即返回的内容必须是合法的 JSON 文本。
	 */
	const QINIU_PP_CALLBACK_URL = 'callbackUrl';
	
	/**
	 * 上传成功后，七牛云向App-Server发送POST请求的数据
	 * 支持魔法变量和自定义变量。
	 * callbackBody 要求是合法的 url query string。
	 * 如：key=$(key)&hash=$(etag)&w=$(imageInfo.width)&h=$(imageInfo.height)。
	 */
	const QINIU_PP_CALLBACK_BODY = 'callbackBody';
	
	/**
	 * 资源上传成功后触发执行的预转持久化处理指令列表
	 * 每个指令是一个API规格字符串，多个指令用“;”分隔。
	 * @link http://developer.qiniu.com/docs/v6/api/reference/security/put-policy.html#put-policy-persistent-ops-explanation
	 */
	const QINIU_PP_PERSISTENT_OPS = 'persistentOps';
	
	/**
	 * 接收预转持久化结果通知的URL
	 * 必须是公网上可以正常进行POST请求并能响应HTTP/1.1 200 OK的有效URL。
	 * 如设置persistenOps字段，则本字段必须同时设置（未来可能转为可选项）。
	 */
	const QINIU_PP_PERSISTENT_NOTIFY_URL = 'persistentNotifyUrl';
	
	/**
	 *  自定义资源名
	 *  支持魔法变量及自定义变量。
	 *  这个字段仅当用户上传的时候没有主动指定key的时候起作用。
	 */
	const QINIU_PP_SAVE_KEY = 'saveKey';
	
	/**
	 * 限定上传文件的大小，单位：字节（Byte）
	 * 超过限制的上传内容会被判为上传失败，返回413状态码。
	 */
	const QINIU_PP_FSIZE_LIMIT = 'fsizeLimit';
	
	/**
	 * 开启MimeType侦测功能
	 * 设为非0值，则忽略上传端传递的文件MimeType信息，使用七牛服务器侦测内容后的判断结果
	 * 默认设为0值，如上传端指定了MimeType则直接使用该值，否则按如下顺序侦测MimeType值：
	 * 1. 检查文件扩展名
	 * 2. 检查Key扩展名
	 * 3. 侦测内容
	 */
	const QINIU_PP_DETECT_MIME = 'detectMime';
	
	/**
	 * 	限定用户上传的文件类型
	 * 指定本字段值，七牛服务器会侦测文件内容以判断MimeType，再用判断值跟指定值进行匹配，匹配成功则允许上传，匹配失败返回403状态码
	 * 示例
	 * 1. “image/*“表示只允许上传图片类型；
	 * 2. “image/jpeg;image/png”表示只允许上传jpg和png类型的图片；
	 * 3. “!application/json;text/plain”表示禁止上传json文本和纯文本（注意最前面的感叹号）。
	 */
	const QINIU_PP_MIME_LIMIT = 'mimeLimit';
	
	/**
	 * 对文件先进行一次变换操作（比如将音频统一转为某种码率的mp3）再进行存储
	 * 本字段的值是一个fop指令，比如
	 * imageView/1/w/310/h/395/q/80
	 * 其含义是对上传文件执行该fop指令，然后把处理结果作为最终资源保存到七牛云。
	 * 须与fopTimeout字段配合使用。
	 */
	const QINIU_PP_TRANSFORM = 'transform';
	
	/**
	 * 文件变换操作执行的超时时间（单位：秒）
	 * 这个值太小可能会导致误判（最终存储成功了但客户端得到超时错），太大可能会导致服务端将其判断为低优先级任务。
	 * 建议取一个相对准确的时间估计值*N（N不要超过5）。
	 * 须与transform字段配合使用。
	 * fopTimeout 如果未指定会有一个默认的超时（我们建议尽量主动指定）。
	 */
	const QINIU_PP_FOP_TIMEOUT = 'fopTimeout';
	
	/**
	 * 存储已经设置的PutPolicy的键值对
	 * @var array
	 */
	private $_put_policy = array();
	
	
	function __construct($config = array()){
		parent::__construct($config);
	}
	
	/**
	 * 如果是直接调用此类库中的常量需要先初始化一下以便能够加载此类库
	 */
	function init(){
		return $this;
	}
	
	/**
	 * 设置一个PutPolicy键值对，设置数组时需要转换成JSON格式
	 * @param string $policy_key
	 * @param mix $policy_val
	 */
	function set_policy($policy_key, $policy_val){
		$this->_put_policy [$policy_key] = $policy_val;
		return $this;
	}
	
	/**
	 * 批量设置上传策略，如上传的值是数组需要转换成JSON格式
	 * @param array $arr_policy
	 * @return Qiniu_put_policy
	 */
	function set_policy_array(array $arr_policy){
		$this->_put_policy = $arr_policy + $this->_put_policy;
		return $this;
	}
	
	/**
	 * 清空已经设置的策略
	 */
	function clear_policy(){
		$this->_put_policy = array();
	}
	
	/**
	 * 根据现有的上传策略生成一个Token，并返回
	 * 在生成一次Token后将会自动清除已经设置的的上传策略
	 */
	function get_token(){
		$pp = json_encode($this->_put_policy);
		$token = $this->auth->sign_with_data($pp);
		
		//清除已经设置的策略
		$this->clear_policy();
		
		return $token;
	}
	
	
	function get_policy(){
		return $this->_put_policy;
	}
	
}