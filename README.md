# Qiniu_API_for_PHP

七牛PHP SDK非官方版本，本打算开发为用于CodeIgniter的类库，后修改为可以通用
于各种PHP环境。

## 声明
1. SDK处在开发中，基本功能可以正常使用，如在使用中发现任何问题或者某些有用的功能未实现或未完善，观迎给作者
发送邮件或QQ联系以尽快改进；
2. 类库的CURL请求和返回数据处理的部分参照（返回内容解析部分为照抄）了GitHub上面hfcorriez大神的代码，在此表示感谢！
hfcorriez大神所写的PHP类库[原地址](https://github.com/hfcorriez/php-qiniu)。
3. 如发现任何问题欢迎联系作者：沈冰翼，QQ：431269018， 邮箱： <jerry@icewingcc.com>，谢谢！

## 优点

1. 使用面向对象的设计，所有操作均有相关的类来完成，并且类文件只有在使用的时候才会被加载，减少系统资源消耗；
2. 子类化所有功能，使SDK更易于扩展；
3. 所有操作同时支持公开和私有的空间，而对函数的调用完全相同；
4. 尽量简化函数的调用过程，只需一条简单的命令便可以完成操作而无需知道具体的实现过程。
5. 允许直接操作非当前空间的文件（即在需要传入文件名作为参数的地方改成包含空间名和文件名的数组，详见[文件操作](#文件操作)）。

## 系统要求

* PHP 5.2版本以上
* 支持CURL扩展



# 目录

+ [基本用法](#基本用法)
	- [初始化](#初始化)
	- [基本输出](#基本输出)
	- [推荐的使用方法](#推荐的使用方法)
+ [文件访问](#文件访问)
    - [生成访问链接](#生成访问链接)
    - [生成下载链接/可选参数](#生成下载链接/可选参数)
    - [获取文件内容](#获取文件内容)
+ [文件操作](#文件操作)
    - [查看文件](#查看文件)
    - [复制文件](#复制文件)
    - [移动/重命名文件](#移动/重命名文件)
    - [删除文件](#删除文件)
    - [抓取网络文件](#抓取网络文件)
    - [列举文件](#列举文件)
    - [继续列举文件](#继续列举文件)
    - [文件批量操作](#文件批量操作)
    - [修改元信息](#修改元信息)
+ [文件上传](#文件上传)
    - [简单上传](#简单上传)
    - [字符串上传](#字符串上传)
    - [高级上传/上传策略](#高级上传/上传策略)
+ [图片处理](#图片处理)
	- [创建缩略图](#创建缩略图)
	- [获取图片信息](#获取图片信息)
	- [高级图像处理](#高级图像处理)


# 使用方法

## 基本用法

### 初始化

把"qiniu.php"连同"qiniu_includes"文件夹一同拷贝到您的项目目录（推荐Libraries目录），包含
类库的主入口文件并实例化一个$qiniu对象。

```php
require 'qiniu.php';

$conf = array(
    'ak' => 'ACCESS KEY', //必须，请输入您的ACCESS KEY
    'sk' => 'SECRECT KEY', //必须，请输入您的SECRECT KEY
    'bucket' => 'BUCKET NAME', //可选，您的空间名（建议填写）
    'auth' => 'public', //可选，空间的访问权限，公开还是私有，默认为公开
    'domain' => '自定义域名' //可选，空间设置的自定义域名，在有导出URL的时候会自动应用该域名
    );
$qiniu = new Qiniu($conf);

//获取文件信息的操作
$ret = $qiniu->rs->stat('abc.jpg');
var_dump($ret);
```

类Qiniu的构造函数接受一个包含配置信息的数组，数组中必须用“ak”和"sk“来指定您的ACCESS KEY 和 SECRECT KEY。

构造函数的参数Bucket是可选的，这意味着您可以在调用函数时手动指定被操作的空间，而不局限于使用默认的空间来操作，这在跨空间的文件管理操作中会显得非常有用。

```php
//CI(CodeIgniter)中加载类库的方法
$conf = array(
    'ak' => 'ACCESS KEY', 
    'sk' => 'SECRECT KEY', 
    'bucket' => 'BUCKET NAME', 
    'auth' => 'public', 
    'domain' => '自定义域名'
    };
$this->load->library('qiniu', $conf);

//获取文件信息的操作
$ret = $this->qiniu->rs->stat('abc.jpg');
var_dump($ret);
```

### 基本输出

类库中除了有些直接返回生成URL的函数（如get_url等）外，基本上都是返回一个Qiniu_response对象，对象中包含的内容如下：

通常情况下在判断返回值为OK时即可用 $ret->data 来获取真正的返回内容。

```
object(Qiniu_response)#5 (6) {
  ["code"]=>int(200)
  ["body"]=>string(106) "{"fsize":122861,"hash":"Fhf2UiLBUz_8eJamHQ2VaXc1wLQM","mimeType":"image/jpeg","putTime":14082922036310155}"
  ["headers"]=>
  array(8) {
    ["server"]=>
    string(11) "nginx/1.4.4"
    ["date"]=>
    string(19) "Sun, 17 Aug 2014 16"
    ["content-type"]=>
    string(16) "application/json"
    ["content-length"]=>
    string(3) "106"
    ["connection"]=>
    string(10) "keep-alive"
    ["cache-control"]=>
    string(8) "no-store"
    ["x-log"]=>
    string(21) "rs6_2.sel;qtbl.get;RS"
    ["x-reqid"]=>
    string(16) "dxsAAHLccP4PQ4sT"
  }
  ["message"]=>
  string(2) "OK"
  ["protocol"]=>
  string(8) "HTTP/1.1"
  ["data"]=>
  array(4) {
    ["fsize"]=>
    int(122861)
    ["hash"]=>
    string(28) "Fhf2UiLBUz_8eJamHQ2VaXc1wLQM"
    ["mimeType"]=>
    string(10) "image/jpeg"
    ["putTime"]=>
    float(1.408292203631E+16)
  }
}

```

### 推荐的使用方法

对于返回内容推荐使用 is_OK()函数来判断是否执行成功。执行成功时 $ret->data即为七牛服务器返回的JSON解析后的数组，$ret->body为JSON原始数据。具体服务器可能返回哪些信息请参考七牛的官方文档。

请求失败时会设置 $ret->data['error']为错误信息。

```php
$ret = $qiniu->rs->stat('abc.jpg');
if($ret->is_OK()){
    $data = $ret->data;
    $fileSize = $data['fsize'];
}
```

## 文件访问

文件访问类（下载类）提供了最基本的生成文件访问链接和下载链接的方式。

### 生成访问链接

唯一必选参数为要获取访问地址的文件名，函数会根据空间的公开和私有属性自动判断URL后面是否需要添加Token。

```php
$url = $qiniu->dl->get_url('abc.jpg');
```

### 生成下载链接/可选参数

`get_url`函数允许传入以下参数：

* 参数一：需要生成访问/下载链接的文件名
* 参数二：是否为下载操作（默认为FALSE，只有在为TRUE时生成下载链接）
* 参数三：下载文件的新文件名（仅在参数二为TRUE时有效）
* 参数四：链接访问的有效期，默认是7200秒（仅在空间为私有时有效）

```php
$url = $qiniu->dl->get_url('abc.jpg', TRUE, 'new_file.jpg', 7200);
```

### 获取文件内容

使用`get_content`函数返回文件的内容，或者指定一个位置，直接把获取到的内容保存到服务器中。

`get_content`接收两个参数：

* 参数一：需要获取内容的文件名
* 参数二：文件将要保存的位置，设置为FALSE时只返回而不保存；设置为一个确切的文件路/路径将会保存内容到这个路径，并在成功时返回内容，失败时返回FALSE
* 返回值：成功返回内容，失败返回FALSE

```php
// 直接返回文件内容
$content = $qiniu->dl->get_content('abc.jpg');
header('Content-Type: image/jpg');
echo $content;

// 保存文件
$ret = $qiniu->dl->get_content('abc.jpg', 'save.jpg');
if($ret){
    echo '保存文件成功';
}
```

## 文件操作

需要注意的是在传入参数中如果要求输入一个文件名，则您可以选择使用以下两种方式：

1. 直接传递文件名为参数，如：
    ```$qiniu->rs->stat('abc.jpg');```,
    这时会使用构造函数中传入的Bucket作为空间名；
2. 手动传入空间名，如：
    ```$qiniu->rs->copy('abc.jpg', array('my-bucket', 'abc.jpg'));```,
    这句代码的意思是把当前空间中的abc.jpg文件复制另一个名为my-bucket的空间下且文件名为abc.jpg。
    
    

### 查看文件

```php
$ret = $qiniu->rs->stat('abc.jpg');
print_r($ret->data);

/* 输出（注意：公开空间和私有空间的输出结果可能会有不同）
Array
(
    [fsize] => 122861
    [hash] => Fhf2UiLBUz_8eJamHQ2VaXc1wLQM
    [mimeType] => image/jpeg
    [putTime] => 1.408292203631E+16
)
*/
```

### 复制文件

* 参数一为要复制的源文件
* 参数二为要复制到的位置及文件名

```php
$ret = $qiniu->rs->copy('abc.jpg', 'abcdef.jpg');
print_r($ret->data);

/* 输出
执行成功data返回空数组，
失败时data['error']将包含错误信息
*/
```


### 移动/重命名文件

* 参数一为要移动/重命名的源文件
* 参数二为移动到的位置/新文件名

```php
$ret = $qiniu->rs->move('abc.jpg', 'abcdef.jpg');
print_r($ret->data);

/* 输出
执行成功data返回空数组，
失败时data['error']将包含错误信息
*/
```

### 删除文件

```php
$ret = $qiniu->rs->delete('abc.jpg');
print_r($ret->data);

/* 输出
执行成功data返回空数组，
失败时data['error']将包含错误信息
*/
```

### 抓取网络文件

* 参数一为要抓取的网络文件的URL
* 参数二为要保存到空间的位置

```php
$ret = $qiniu->rs->fetch('http://www.baidu.com/img/bd_logo.png', 'baidulogo.png');
print_r($ret->data);

/* 输出
执行成功data返回空数组，
失败时data['error']将包含错误信息
*/
```

### 列举文件

列举出七牛空间中指定的文件资源，并支持分批列举（分页）

* 参数一：指定前缀，只有资源名匹配该前缀的资源会被列出。缺省值为空
* 参数二：【可选】列举的条目数，范围1-1000，默认为1000
* 参数三：【可选】指定目录分隔符，列出所有公共前缀（模拟列出目录效果），默认为空
* 参数四：【可选】指定列举文件的空间，默认为预设的空间
* 参数五：【可选】上一次列举返回的位置标记（不建议使用，可用ls_resume函数代替）

```php
$ret = $qiniu->rs->ls('201408');

if($ret->is_OK()){
	print_r($ret->data['items']);
```

### 继续列举文件

如果上一步执行的列举文件（ls）操作中指定了分页，或者需要返回的文件数量大于默认的1000个文件时将会产生分页。循环调用此函数可依次获取所有内容。函数将在没有新内容时返回FALSE

```php
//指定获取前缀为201408的文件，并且每次只获取5个
$ret = $qiniu->rs->ls('201408', 5);
print_r($ret->data['items']);

//循环获取所有内容并输出
while($new = $qiniu->rs->ls_resume()){
	print_r($new->data['items']);
}
```

### 文件批量操作

批量操作允许在一次请求内处理多个文件操作。您需要使用`batch_add_stat`、`batch_add_move`、`batch_add_copy`、`batch_add_delete`来添加操作到队列中，并使用`do_batch`来应用这些操作。支持链式操作。

```php
$qiniu->rs->batch_add_stat('abc.jpg')
          ->batch_add_copy('abc.jpg', 'abcde.jpg')
          ->batch_add_move('abcde.jpg', 'ddd.jpg')
          ->batch_add_delete('ddd.jpg')
          ->do_batch();
```

### 修改元信息

主动修改指定资源的文件类型,即mineType

```php
$qiniu->rs->change_meta('abc.jpg', 'image/png');
```


## 文件上传

### 简单上传

简单上传适用于将本地文件（服务器上的文件）直接上传到七牛云存储。

* 参数一：要上传的本地文件（文件路径）
* 参数二：要存储为的文件名（如果让七牛设置可将此参数置为空）
* 参数三：要上传到的空间名（为空时表示使用默认的Bucket）
* 参数四：是否使用自定义的上传策略（默认为否，适用于默认的上传策略不能满足要求时）
* 参数五：上传执行的有效期（默认为7200秒）

```php
$ret = $qiniu->upload->upload('abc.jpg', 'newfilename.jpg');
print_r($ret->data);
/*
使用默认上传策略的情况下会在上传成功后返回被保存文件的文件名和HASH值
data=array (
["hash"]=>
"FoZt6eX5BQno33iokM9_Diq30Mhj"
["key"]=>
"newfilename.jpg"
)
*/
```

### 字符串上传

字符串上传适用于保存一个字符串到七牛，如设置、用户提交的文本等

* 参数一：要上传的字符串
* 参数二：要存储为的文件名（如果让七牛设置可将此参数置为空）
* 参数三：要上传到的空间名（为空时表示使用默认的Bucket）
* 参数四：是否使用自定义的上传策略（默认为否，适用于默认的上传策略不能满足要求时）
* 参数五：上传执行的有效期（默认为7200秒）

```php
$string = "This is my test string";

$ret = $qiniu->upload->upload_string($string, 'mytestfile.txt');
print_r($ret->data);
```

### 高级上传/上传策略

使用上传策略来控制具体的上传行为以及上传完成后服务器如何操作（详见七牛官方文档[上传策略](http://developer.qiniu.com/docs/v6/api/reference/security/put-policy.html)）。上传策略类并没有做过多的封装，目的就是提供一种可用于自定义所有上传细节的类。

上传策略类根据官网中有说明的每一个策略分别创建一个常量，使用这些常量可以避免传值过程中的拼写错误，如果需要使用这些常量的话您需要在调用上传策略类前初始化它：

```php
$qiniu->put_policy->init();
```

可以单独设置某一个上传策略，或者通过数组的形式批量设置它们，支持链式操作：

```php
//使用链式操作设置所需的策略
$qiniu->put_policy->set_policy(Qiniu_put_policy::QINIU_PP_SCOPE, '<bucket>:<key>')
        ->set_policy(Qiniu_put_policy::QINIU_PP_DEADLINE, time() + 7200)
        ->set_policy(Qiniu_put_policy::QINIU_PP_SAVE_KEY, 'newFileName.jpg');

//使用批量设置
$arr = array(
    Qiniu_put_policy::QINIU_PP_SCOPE => '<bucket>:<key>',
    Qiniu_put_policy::QINIU_PP_DEADLINE => time()+7200,
    Qiniu_put_policy::QINIU_PP_SAVE_KEY => 'newFileName.jpg'
    );
$qiniu->put_policy->set_policy_array($arr);
```

设置好上传策略后可以使用`get_token()`函数生成基于刚才设置的策略的Token：

```php
$token = $qiniu->put_policy->get_token();
```

当然，获取上传Token的意义肯定还在于表单上传：

```html
<form method="post" action="http://upload.qiniu.com/" enctype="multipart/form-data">
    <input name="token" type="hidden" value="<?php echo $token;?>">
    <input name="file" type="file" />
    <input type="submit" />
</form>
```

## 图片处理

图片处理类函数使用七牛原生的处理方式，并且同时支持公开空间和私有空间。

### 创建缩略图

参数一：生成缩略图操作的文件名
参数二：生成选项（数组）,详见[官方文档](http://developer.qiniu.com/docs/v6/api/reference/fop/image/imageview2.html)。

```php
$url = $qiniu->image->view('abc.jpg', array(
    'mode' => 1,//生成模式
    'width' => 400,//缩略图宽
    'height' => 300,//缩略图高
    'q' => 85,//质量
    'format' => 'png',//文件格式
    'interlace' => 0//是否为渐进式图像
    );
echo $url;
```

### 获取图片信息

返回包含图像基本信息（如格式、大小、颜色等）的数组，详见[官方文档](http://developer.qiniu.com/docs/v6/api/reference/fop/image/imageinfo.html)。

```php
$info = $qiniu->image->info('abc.jpg');
print_r($info);
```

### 高级图像处理

* 参数一：需要处理的图片
* 参数二：数组，键名为要执行的操作，值为操作的参数；对于不需要参数的操作可将值置为空字符串。
详见[官方文档](http://developer.qiniu.com/docs/v6/api/reference/fop/image/imagemogr2.html)。

```php
$url = $qiniu->image->mogr('abc.jpg', array(
    'strip' => '',
    'thumbnail' => '300x300',
    'blur' => '3x5'
    );
echo $url;
```

