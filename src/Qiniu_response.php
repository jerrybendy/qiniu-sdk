<?php
/**
 * Created by PhpStorm.
 * User: jerry
 * Date: 16/4/30
 * Time: 23:46
 */

namespace Jerrybendy\Qiniu;



/**
 * HTTP响应相关类
 *
 * @since v1.0
 */
class Qiniu_response
{

    public $code;  //返回码  int

    public $body;  //返回的内容 string | json

    public $headers = array();  //HTTP头   array

    public $message;

    public $protocol;

    public $data = array();  // body  array


    /**
     * 通过CURL返回的内容构造Response对象
     *
     * @since v1.0
     * @param string $response
     */
    public function __construct($response)
    {
        if (is_string($response) && ($parsed = $this->parse($response))) {
            foreach ($parsed as $key => $value) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * 获取指定的头部信息
     *
     * @since v1.0
     * @param string $key
     * @param string $default
     * @return string
     */
    public function header($key, $default = NULL)
    {
        $key = strtolower($key);

        return !isset($this->headers[$key]) ? $this->headers[$key] : $default;
    }

    /**
     * 解析CURL返回的文本
     *
     * @since v1.0
     * @param $response
     * @return array
     */
    private function parse($response)
    {
        //解析Header的内容
        $body_pos = strpos($response, "\r\n\r\n");
        $header_string = substr($response, 0, $body_pos);
        if ($header_string == 'HTTP/1.1 100 Continue') {
            $head_pos = $body_pos + 4;
            $body_pos = strpos($response, "\r\n\r\n", $head_pos);
            $header_string = substr($response, $head_pos, $body_pos - $head_pos);
        }
        $header_lines = explode("\r\n", $header_string);

        $headers = array();
        $code = FALSE;
        $body = FALSE;
        $protocol = NULL;
        $message = NULL;
        $data = array();

        foreach ($header_lines as $index => $line) {
            if ($index === 0) {
                preg_match('/^(HTTP\/\d\.\d) (\d{3}) (.*?)$/', $line, $match);
                list(, $protocol, $code, $message) = $match;
                $code = (int)$code;
                continue;
            }
            list($key, $value) = explode(":", $line);
            $headers[strtolower(trim($key))] = trim($value);
        }

        if (is_numeric($code)) {
            $body_string = substr($response, $body_pos + 4);
            if (!empty($headers['transfer-encoding']) && $headers['transfer-encoding'] == 'chunked') {
                $body = $this->decodeChunk($body_string);
            } else {
                $body = (string)$body_string;
            }
            $result['header'] = $headers;
        }

        // 自动解析数据
        if (strpos($headers['content-type'], 'json')) {
            $data = json_decode($body, TRUE);
        }

        return $code ? array(
            'code'     => $code,
            'body'     => $body,
            'headers'  => $headers,
            'message'  => $message,
            'protocol' => $protocol,
            'data'     => $data
        ) : FALSE;
    }

    /**
     * Decode chunk
     *
     * @since v1.0
     * @param $str
     * @return string
     */
    private function decodeChunk($str)
    {
        $body = '';
        while ($str) {
            $chunk_pos = strpos($str, "\r\n") + 2;
            $chunk_size = hexdec(substr($str, 0, $chunk_pos));
            $str = substr($str, $chunk_pos);
            $body .= substr($str, 0, $chunk_size);
        }

        return $body;
    }

    /**
     * 检查CURL请求是否返回了成功（200）
     *
     * @since v1.0
     * @return bool
     */
    public function is_OK()
    {
        return ($this->code >= 200 && $this->code <= 299) ? TRUE : FALSE;
    }


}