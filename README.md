# jerrybendy/qiniu-sdk

七牛PHP SDK非官方版本

## 声明
1. SDK处在开发中，基本功能可以正常使用，如在使用中发现任何问题或者某些有用的功能未实现或未完善，观迎给作者
发送邮件或QQ联系以尽快改进；
2. 类库的CURL请求和返回数据处理的部分参照（返回内容解析部分为照抄）了GitHub上面hfcorriez大神的代码，在此表示感谢！
hfcorriez大神所写的PHP类库[原地址](https://github.com/hfcorriez/php-qiniu)。
3. 如发现任何问题欢迎联系作者：冰翼，QQ：431269018， 邮箱： <jerry@icewingcc.com>，谢谢！

### 优点

1. 子类化所有功能，使SDK更易于扩展；
2. 所有操作同时支持公开和私有的空间，而对函数的调用完全相同；
3. 尽量简化函数的调用过程，只需一条简单的命令便可以完成操作而无需知道具体的实现过程。
4. 允许直接操作非当前空间的文件（即在需要传入文件名作为参数的地方改成包含空间名和文件名的数组，详见[文件操作](documents/#文件操作)）;
5. 支持自定义上传策略, 可以自由控制上传的每个细节;

### 系统要求

* PHP 5.3.9版本以上
* 支持CURL扩展

### 使用方法

请添加`jerrybendy/qiniu-sdk`到你的`composer.json`文件中, 并执行`composer install`.

```json
"require": {
    "jerrybendy/qiniu-sdk": "*"
}
```


+ [基本用法](documents/基本用法)
	- [初始化](documents/基本用法#初始化)
	- [基本输出](documents/基本用法#基本输出)
	- [推荐的使用方法](documents/基本用法#推荐的使用方法)
+ [文件访问](documents/文件访问)
    - [生成访问链接](documents/文件访问#生成访问链接)
    - [生成下载链接/可选参数](documents/文件访问#生成下载链接/可选参数)
    - [获取文件内容](documents/文件访问#获取文件内容)
+ [文件操作](documents/文件操作)
    - [查看文件](documents/文件操作#查看文件)
    - [复制文件](documents/文件操作#复制文件)
    - [移动/重命名文件](documents/文件操作#移动/重命名文件)
    - [删除文件](documents/文件操作#删除文件)
    - [抓取网络文件](documents/文件操作#抓取网络文件)
    - [列举文件](documents/文件操作#列举文件)
    - [继续列举文件](documents/文件操作#继续列举文件)
    - [文件批量操作](documents/文件操作#文件批量操作)
    - [修改元信息](documents/文件操作#修改元信息)
+ [文件上传](documents/文件上传)
    - [简单上传](documents/文件上传#简单上传)
    - [字符串上传](documents/文件上传#字符串上传)
    - [高级上传/上传策略](documents/文件上传#高级上传/上传策略)
+ [图片处理](documents/图片处理)
	- [创建缩略图](documents/图片处理#创建缩略图)
	- [获取图片信息](documents/图片处理#获取图片信息)
	- [高级图像处理](documents/图片处理#高级图像处理)
	- [获取图片EXIF](documents/图片处理#获取图片EXIF)

### [版本记录](https://github.com/jerrybendy/qiniu-sdk/wiki/change-history)


## LICENSE

The MIT License (MIT)

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
the Software, and to permit persons to whom the Software is furnished to do so,
subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.