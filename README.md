# BOOKY

基于[wulaphp](https://github.com/ninggf/wulaphp)的文档生成器。

## 安装

1. `composer require wula/booky -vvvv`
    * 请耐心等待,有时可能会很慢，很慢。。。
2. `vender/bin/wulaphp init`
    * 初始化wulaphp目录结构。
3. `vender/bin/wulaphp conf`
    * 获取`nginx`或`httpd`的配置，复制并配置你的`nginx`或`httpd`。
 
## 创建文档目录结构

执行以下命令创建文档目录结构:

> `./artisan booky init`

`booky init`命令将创建`doc`目录并添加以下文件:

1. `_summary.md` 文档导航菜单
2. `index.md`  默认文档首页


同时生成文档配置文件: `conf/booky_config.php`。

## 配置

```php
<?php

return [
  'dir'      => 'doc',
  'theme'    => 'wula',
  'summary'  => ['l1cls' => '', 'l2cls' => '', 'acls' => '', 'ccls' => ''],
  'theme_en' => 'wen',
  'plugins'  => [],
  'langs'    => ['default' => '中文', 'en' => '英文'],  
];
```

1. `dir`: 文档目录。
2. `theme`: 默认语言的模板主题
3. `theme_en`: 语言`en`的模板主题
4. `plugins`: 可以配置自己的插件.
    * 插件的全类名
5. `summary` : 配置导航菜单ul,a标签类
    * `l1cls`: 第一级导航ul标签类
    * `l2cls`: 第二级导航ul标签类
    * `l3cls`: 第三级导航ul标签类
    * `acls`: `A`标签类
    * `ccls`: 当前`A`标签类

其它配置，请根据需要自行添加，模板中通过`$config`变量引用.

## 文档导航菜单(_summary.md)

在`_summary.md`文件中通过markdown的列表语法定义文档导航菜单:

```markdown

- [简介](intro.md)
- [安装](install.md)
    * [安装步骤一](install1.md)
    * [安装步骤二](install2.md)   
- [...](xxx.md)  

```

模板中通过`$summary`引用, `booky`生成的HTML代码如下:

```html
<ul class="navi-ul-1">
    <li class="active">
        <a class="active" href="..." >adfads</a>
        <ul class="navi-ul-2">
            <li> <a href="aaa">adfasdf</a> </li>
            <li> <a href="adsfasdf">adfasdf</a> </li>
        </ul>
    </li>
    <li></li>
    <li></li>
</ul>
```

请通过CSS来美化导航菜单.

## 文档编写

请使用[markdown](https://daringfireball.net/projects/markdown/syntax)语法(支持[extra](https://michelf.ca/projects/php-markdown/extra/))编写你的文档。

### 自定义文档变量

可以通过在文档头部添加`yaml`格式的字符串为文档添加自定义配置，格式如下:

```yaml

---
title: 文档标题
demo: [0|1] #是否生成html演示代码,不定义时默认为不生成演示代码.
layout: page1 # 页面使用的模板
index: abc adf efds # 全文索引关键词（字）
---

```

除以上配置外，可任意添加配置,在模板中通过`$page`变量引用,如:`$page.title`为文档标题。

### 资源引用

在md文件中直接基于相对目录进行资源引用即可，如:

```markdown

[更多说明](../more.md)
![图片1](imgs/img.pnp)

```

### TOC

`booky`会根据文档正文的标题生成相应的`toc`，可以通过`$page.tocStr`或`$page.toc`在模板中引用。生成toc时会忽略掉跳级的标题。

`tocStr`片断如下:

```html

<ul class="toc">
    <li>
        <a id="ref-header1" href="#header1">Header Text</a>
        <ul class="toc-1">
            <li>
            <a id="ref-header11" href="#header11">Header11 Text</a>
            </li>
        </ul>
    </li>
    <li><a href="#" id="ref-header2">Header2 Text</a></li>
</ul>

```

请使用CSS美化它.

## 模板

booky使用`smarty`模板引擎，模板文件位于`themes`目录中。

模板变量:

1. `$summary`: 目录导航菜单
2. `$url`: 当前页面的url
3. `$config`: 配置，是个数组。
4. `$nextPage`，`$prevPage`: 上一个文档，下一个文档
    * `url`: URL
    * `name`: 标题
3. `$page`: 页面
    * `title`: 页面标题
    * `tocStr`: toc HTML片断
    * `toc`: toc 实例，可遍历
    * `content`: 文档正文
    * 其它自定义变量
    
### URL生成与资源引用

在模板中通过`docurl`修饰器生成文档或资源的url，如：

```smarty

<a href="{'help.md'|docurl}">帮助</a>

<img src="{'logo.png'|docurl}"/>

```

> URL会基于文档根目录生成。

## 搜索

`BOOKY`提供了搜索接口，可以通过ajax方法访问`/search-doc?q=`进行搜索，`q`为搜索词,搜索结果如下:

```json

{
    "hits":2,
    "execution_time":"1.399 ms",
    "q":"标",
    "pages":[
        {
            "url":"/aa.html",
            "title":"厉害的一篇文标 and php"
        },
        {
            "url":"/",
            "title":"厉害啊"
        }
    ]
}

```

### 生成索引

在使用搜索功能之前，需要通过:

`./artisan booky index`

生成文档的搜索索引，每次新增、修改或删除文档后都需要执行此命令重新生成索引。

## 插件

写一个类实现`\wula\booky\plugin\Plugin`接口，并将类的全类名配置到`plugins`数组中即可。

## 高级

为了加快文档资源的访问，请修改相应的配置

### nginx

在配置文件中找到:

`location ~ ^/(modules|themes)/.+\.(js|css|png|gif|jpe?g)$ {` 

修改为:

`location ~ ^/(modules|themes|doc)/.+\.(js|css|png|gif|jpe?g)$ {` 

### httpd

在配置文件中找到:

`AliasMatch "^/(modules|themes)/(.+\.(js|css|jpe?g|png|gif))$"`

修改为:

`AliasMatch "^/(modules|themes|doc)/(.+\.(js|css|jpe?g|png|gif))$"`
