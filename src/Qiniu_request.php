<?php
/**
 * Created by PhpStorm.
 * User: jerry
 * Date: 16/4/30
 * Time: 23:46
 */

namespace Jerrybendy\Qiniu;



/**
 * 发送HTTP请求相关的类
 * 以及CURL操作函数
 */
class Qiniu_request
{
    //请求的URL地址
    public $url;
    //请求的内容主题
    public $body;
    //请求包含的header
    public $header = array();


    public function __construct($url, $body = '')
    {
        $this->url = $url;
        $this->body = $body;
    }

    /**
     * 设置一个HTTP头
     *
     * @since v1.0
     * @param $name
     * @param $val
     */
    public function set_header($name, $val)
    {
        $this->header [$name] = $val;
    }

// 	function set_post_data($data){

// 	}

    /**
     * 发送一个HTTP请求，并返回结果对应的Qiniu_response对象
     *
     * @since v1.0
     *
     * @param string $method 请求发出的方式,默认是POST方式
     *
     * @return Qiniu_response
     * @throws Qiniu_Exception
     */
    public function make_request($method = 'POST')
    {
        $ch = curl_init();
        $options = array(
            CURLOPT_RETURNTRANSFER => TRUE,
            CURLOPT_HEADER         => TRUE,
            CURLOPT_SSL_VERIFYPEER => FALSE,
            CURLOPT_SSL_VERIFYHOST => FALSE,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_URL            => $this->url
        );

        if (!empty($this->header)) {
            //设置默认的Content-Type头
            if (!isset($this->header['Content-Type']))
                $this->header['Content-Type'] = 'application/x-www-form-urlencoded';

            $header = array();
            foreach ($this->header as $key => $parsedUrlValue) {
                $header[] = "$key: $parsedUrlValue";
            }
            $options[CURLOPT_HTTPHEADER] = $header;
        }

        if (!empty($this->body)) {
            $options[CURLOPT_POSTFIELDS] = $this->body;
        }


        @curl_setopt_array($ch, $options);
        $result = @curl_exec($ch);
        $ret = curl_errno($ch);
        if ($ret !== 0) {
            throw new Qiniu_Exception('An CURL error has occured, ' . curl_error($ch));
        }

        /**
         * 2014-6-26
         * 修改此处的传值方式，Response类的构造函数改为接收CURL的返回值
         */
        $resp = new Qiniu_response($result);

// 		$resp->Body = $result;
// 		$resp->StatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
// 		$resp->Header['Content-Type'] = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        return $resp;
    }


}
