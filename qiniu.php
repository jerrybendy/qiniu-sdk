<?php
/**
 * 七牛云存储API入口文件
 * 
 * 目前已实现文件管理的全部操作
 * 
 * 
 * @author       Jerry
 * @link            http://blog.icewingcc.com
 * @package    Qiniu
 * @since          Version 1.0
 *
 * 最后更新：2014-4-9 
 */

/**
 * 定义七牛云存储的Access Key和Secret Key
 */
define('QINIU_ACCESS_KEY', ''); //此处填入七牛的Access Key
define('QINIU_SECRET_KEY', ''); //此处填入七牛的Secret Key
define('QINIU_DEFAULT_BUCKET', ''); //此入填入默认的Bucket名称

//包含必须文件
define('QINIU_INCLUDE_PATH', dirname(__FILE__) . '/qiniu_includes/');
require_once(QINIU_INCLUDE_PATH . 'qiniu_core.php');
//require_once(QINIU_INCLUDE_PATH . '.php');

class Qiniu{
    
	/**
	 * 定义保存AK和SK的两个保护变量，这两个变量可以在常量定义中设置，也可以在
	 * 这个类被构造的时候被设置，构造函数中的设置值总是优先于常量中定义的值
	 */
	protected $_ak;
	protected $_sk;
	
	/**
	 * 定义一个操作默认使用的空间名称，这个名称可以在常量中定义
	 * 在实际函数操作过程中可以通过指定$opt附加参数的形式使用其它Bucket进行操作
	 */
	protected $_bucket;
	
	/**
	 * 已经被包含（加载）的七牛类库会以数组元素的形式将类对象存储于此
	 * 这样方便以CI原生的方式调用子类库中的函数，并且保证了只有被使用到的类库才会被加载
	 */
	protected static  $_includes = array();
	
	/**
	 * 类的构造函数，如果有提前定义ACCESS KEY和SECRET KEY的话这里可以不加参数
	 * @param array $config 为空时使用前面定义的设置，否则必须包含ak和sk两个元素
	 * 										并且参数中的ak和sk的设置总是优先于常量中的定义
	 * 										可选的bucket参数用于指定默认使用的空间名称
	 * @throws Exception
	 */
	function __construct($config = array()){
		if (isset($config['ak']) && isset($config['sk']) && $config['ak'] && $config['sk']){
			$this->_ak = $config['ak'];
			$this->_sk = $config['sk'];
		} elseif(QINIU_ACCESS_KEY && QINIU_SECRET_KEY){
			$this->_ak = QINIU_ACCESS_KEY;
			$this->_sk = QINIU_SECRET_KEY;
		} else {
			throw new Qiniu_Exception('Access Key or Secret Key is invalid!');
		}
		
		if(isset($config['bucket']) && $config['bucket']){
			$this->_bucket = $config['bucket'];
		} elseif (QINIU_DEFAULT_BUCKET){
			$this->_bucket = QINIU_DEFAULT_BUCKET;
		} else {
			$this->_bucket = '';
		}
	}
	
	

	/**
	 * 此函数允许使用 $this->qiniu->xx->xxxx();的形式调用子库中的函数
	 * @param string $name 要调用的子库的名称
	 * @return 返回子库的对象
	 */
	function __get($name){
		return $this->_load_class($name);
	}
	
	/**
	 * 载入一个七牛类库，并返回载入的类库
	 * @param $class_name
	 */
	protected function _load_class($class_name, $alias = ''){
		if(array_key_exists($class_name, self::$_includes))
			return self::$_includes[$class_name];
		
		if( !empty($alias) && array_key_exists($alias, self::$_includes))
			return self::$_includes[$alias];
		
		if(file_exists(QINIU_INCLUDE_PATH . 'qiniu_' . $class_name . '.php')){
			require_once QINIU_INCLUDE_PATH . 'qiniu_' . $class_name . '.php';
			$full_class_name = 'Qiniu_' . $class_name;
			$obj = new $full_class_name(array('ak'=>$this->_ak, 'sk'=>$this->_sk, 'bucket'=>$this->_bucket));
			
			if( empty($alias))
				self::$_includes[$class_name] = $obj;
			else 
				self::$_includes[$alias] = $obj;
			
			return $obj;
		} else {
			throw new Qiniu_Exception("Cannot find required file 'qiniu_{$class_name}.php'");
		}
	}

	
	/**
	 * 供子类调用的函数，把参数中传入的filename统一输出成数组的形式
	 * 并且数组的第一个元素是BUCKET名称，第二个元素是文件名
	 * @param string|array $filename 传入的字符串或数组
	 * @return array
	 */
	protected function _filename_to_array($filename){
		if(is_array($filename) && count($filename) >1){
			$bucket = $filename[0];
			$key = $filename[1];
		} else {
			$bucket = $this->_bucket;
			$key = $filename;
		}
// 		$key = urlencode($key);
		return array('bucket'=>$bucket, 'key'=>$key);
	}
	
}