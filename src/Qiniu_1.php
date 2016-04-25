<?php
/**
 * 七牛云存储API入口文件
 *
 *
 * @author       Jerry Bendy
 * @link         http://blog.icewingcc.com
 * @package      Qiniu
 * @version      Version 1.1.2
 *
 * 最后更新：2015-5-8
 */


namespace Jerrybendy\Qiniu;


class Qiniu
{

    /**
     * 定义保存AK和SK的两个保护变量，可以在
     * 这个类被构造的时候被设置
     */
    protected $_ak;
    protected $_sk;

    /**
     * 定义一个操作默认使用的空间名称，
     * 在实际函数操作过程中可以通过指定$opt附加参数的形式使用其它Bucket进行操作
     */
    protected $_bucket;

    /**
     * 定义Bucket的公开权限，即 public 还是 private
     * 这将会影响程序在执行过程中具体使用哪种方式实现
     * 默认情况下将会设置此值为公开“public”
     */
    protected $_auth = 'public';

    /**
     * 自定义域名，如 http://res.icewingcc.com/
     * 必须是含 http:// 前缀和后面左斜线的URL形式
     * 如果没有自定义域名则保持此选项为空
     *
     * @var string
     */
    protected $_domain = NULL;

    /**
     * 已经被包含（加载）的七牛类库会以数组元素的形式将类对象存储于此
     * 这样方便以CI原生的方式调用子类库中的函数，并且保证了只有被使用到的类库才会被加载
     */
    protected static $_includes = array();

    #----------------------------------------------------------------
    /**
     * 构造函数
     *
     * @since v1.0
     * @param array $config       必须包含ak和sk两个元素
     *                            可选的bucket参数用于指定默认使用的空间名称
     *                            可选的 auth 参数指定空间的访问权限，默认为public
     *                            可选的 domain 参数指定空间的自定义域名（目前的七牛版本不再
     *                            使用之前的域名规则，所以此参数应为必填项）
     * @throws Qiniu_Exception
     */
    function __construct($config = array())
    {
        if (is_array($config)) {
            //检查AK和SK
            if (isset($config['ak']) && isset($config['sk']) && $config['ak'] && $config['sk']) {
                $this->_ak = $config['ak'];
                $this->_sk = $config['sk'];
            } else {
                throw new Qiniu_Exception('Access Key or Secret Key is invalid!');
            }

            //检查并设置Bucket
            if (isset($config['bucket']) && $config['bucket']) {
                $this->_bucket = $config['bucket'];
            } else {
                $this->_bucket = '';
            }

            //空间权限
            if (isset($config['auth']) && $config['auth']) {
                $this->_auth = ($config['auth'] == 'private') ? 'private' : 'public';
            } else {
                $this->_auth = 'public';
            }

            //自定义域名
            if (isset($config['domain']) && $config['domain'])
                $this->_domain = rtrim($config['domain'], '/') . '/';

        } elseif ($config instanceof Qiniu) {
            $this->_ak = $config->_ak;
            $this->_sk = $config->_sk;
            $this->_bucket = $config->_bucket;
            $this->_auth = $config->_auth;
            $this->_domain = $config->_domain;
        }
    }

    #----------------------------------------------------------------
    /**
     * 此函数允许使用 $this->qiniu->xx->xxxx();的形式调用子库中的函数
     *
     * @since    v1.0
     * @param string $class_name
     * @return object 返回子库的对象
     * @throws Qiniu_Exception
     * @internal param string $name 要调用的子库的名称
     */
    function __get($class_name)
    {
        if (array_key_exists($class_name, self::$_includes)) {
            return self::$_includes[$class_name];
        }

        $full_class_name = 'Qiniu_' . $class_name;

        /*
         * 尝试使用自动加载这个类文件, 如果加载失败将会抛出异常
         */
        try {
            $obj = new $full_class_name($this);

            self::$_includes[$class_name] = $obj;

            return $obj;

        } catch (\Exception $e) {
            throw new Qiniu_Exception("Cannot find required file 'qiniu_{$class_name}.php'");
        }

    }

    #----------------------------------------------------------------
    /**
     * 对于实现对不同访问权限专用函数的调用，如访问的某个函数不存在的话会自动
     * 尝试调用 func_public()，如果函数不存在则会调用 func_private()函数
     * 如果两个函数都不存在的话抛出一个错误
     *
     * @since v1.0
     * @param string $func   函数名
     * @param array  $params 调用的参数
     * @return mixed
     * @throws Qiniu_Exception
     */
    function __call($func, $params)
    {
        if (method_exists($this, $func . '_' . $this->_auth)) {
            return call_user_func_array(array($this, $func . '_' . $this->_auth), $params);
        } else {
            throw new Qiniu_Exception('Call to undefined method ' . $func);
        }
    }

    #----------------------------------------------------------------
    /**
     * 供子类调用的函数，把参数中传入的filename统一输出成数组的形式
     * 并且数组的第一个元素是BUCKET名称，第二个元素是文件名
     *
     * @since v1.0
     * @param string|array $filename 传入的字符串或数组
     * @return array
     */
    protected function _filename_to_array($filename)
    {
        if (is_array($filename) && count($filename) > 1) {
            list($bucket, $key) = $filename;

        } else {
            $bucket = $this->_bucket;
            $key = $filename;
        }

        return array('bucket' => $bucket, 'key' => $key);
    }

}