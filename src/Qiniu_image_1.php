<?php

namespace Jerrybendy\Qiniu;

/**
 * 七牛云存储缩略图生成模块
 *
 * @author          Jerry
 * @link            http://blog.icewingcc.com
 * @package         Qiniu
 * @version         Version 1.1
 *
 * 最后更新：2014-07-08
 *       创建类
 */
class Qiniu_image extends Qiniu
{

    /**
     * 定义添加水印的位置,位置使用九宫格的编号来排序
     *
     * @var array
     */
    protected $_position_string = array(
        1 => 'NorthWest',
        2 => 'North',
        3 => 'NorthEast',
        4 => 'West',
        5 => 'Center',
        6 => 'East',
        7 => 'SouthWest',
        8 => 'South',
        9 => 'SouthEast'
    );


    public function __construct($config = array())
    {
        parent::__construct($config);

    }


    /**
     * 创建一个图像的缩略图
     *
     * @since v1.1
     *
     * @param string|array $filename 要创建缩略图的文件名
     * @param array        $opt      创建时的选项，要求一个以下格式的数组：
     *                               array('mode'=>1, 'width'=>200, 'height' =>200, 'q'=>85, 'format'=>'png',
     *                               'interlace' =>0)
     * @return string
     */
    public function view($filename, array $opt)
    {
        $opt_default = array('mode' => 1);
        $opt = $opt + $opt_default;

        $query = 'imageView2/' . $opt['mode'];
        if (isset($opt['width']))
            $query .= '/w/' . $opt['width'];
        if (isset($opt['height']))
            $query .= '/h/' . $opt['height'];
        if (isset($opt['q']))
            $query .= '/q/' . $opt['q'];
        if (isset($opt['format']))
            $query .= '/format/' . $opt['format'];
        if (isset($opt['interlace']))
            $query .= '/interlace/' . $opt['interlace'];

        $url = $this->dl->get_url_public($filename) . '?' . $query;

        if ($this->_auth == 'private') {
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
     *
     * @since v1.1
     *
     * @param string|array $filename 文件名
     * @return mixed|boolean
     */
    public function info($filename)
    {
        $url = $this->dl->get_url_public($filename) . '?imageInfo';

        if ($this->_auth == 'private') {
            $token = $this->auth->sign($url);
            $url = "{$url}&token={$token}";
        }

        $ret = @file_get_contents($url);
        if ($ret) {
            $con = json_decode($ret, TRUE);

            return $con;
        } else {
            return FALSE;
        }

    }


    /**
     * 七牛图像高级操作，详细参数见七牛官方文档
     *
     * @link  http://developer.qiniu.com/docs/v6/api/reference/fop/image/imagemogr2.html
     * @since v1.1
     *
     * @param string|array $filename 文件名
     * @param array        $opt      参数数组，详见文档（没有值的参数可以将数组值设为空，如 'strip'=>''
     * @return string
     */
    public function mogr($filename, array $opt)
    {
        $query = 'imageMogr2/';

        foreach ($opt as $key => $val) {
            $query .= "/{$key}" . (empty($val) ? '' : "/{$val}");
        }

        $url = $this->dl->get_url_public($filename) . '?' . $query;

        if ($this->_auth == 'private') {
            $token = $this->auth->sign($url);

            return "{$url}&token={$token}";
        }

        return $url;
    }


    /**
     * 获取指定图片资源的EXIF信息
     *
     * @since v1.2
     *
     * @param $filename
     * @return Qiniu_response
     * @throws Qiniu_Exception
     */
    public function exif($filename)
    {

        $url = $this->dl->get_url($filename, FALSE, NULL, 7200, 'exif');

        $req = new Qiniu_request($url);

        $resp = $req->make_request('GET');

        return $resp;

    }


    public function water_mark_image($filename, $image, $position = 7, $opacity = 100, $offsetX = 10, $offsetY = 10)
    {

        if (is_int($position)) {
            if ($position > 0 && $position < 10) {
                $position = $this->_position_string[$position];
            } else {
                throw new Qiniu_Image_Exception('Water mark position only can be an int or string value, and must between 1 and 9');
            }
        }


    }


}