=== Plugin Name ===
Contributors: robbliu
Tags: doubanshow,douban,page
Donate link: http://www.robb.com.cn/plugins/
Requires at least: 2.0
Tested up to: 2.9.2
Stable tag: 1.0

Show your douban's collections and recommendations in a page.

== Description ==

Show your douban's collections and recommendations in a page.

WP豆瓣秀插件可以通过豆瓣API调取你的各种收藏和推荐在独立页面展示。


**Supported Languages:**

* US English/en_US (default)
* 简体中文/zh_CN (translate by [robb](http://www.robb.com.cn/))
* Russian/ru_RU (translate by [Fat Cow](http://www.fatcow.com/))

**Demo:**

[http://www.robb.com.cn/douban/](http://www.robb.com.cn/douban/)

== Installation ==
<ol>
<li>Unzip wp-doubanshow to the '/wp-content/plugins/' directory</li>
<li>Activate the plugin through the 'Plugins' menu in WordPress</li>
<li>Update settings in DoubanShow Options</li>
<li>Create a page temaplate with function &lt;?php doubanshow();?&gt;.</li>
<li>Create a page using the page template just create.</li>
<li>Any problem, please contact @<a href="http://twitter.com/robb">robb</a> at Twitter.</li>
</ol>

<ol>
<li>解压缩后将文件上传到'/wp-content/plugins/'目录</li>
<li>在插件中激活wp-doubanshow</li>
<li>在菜单中的豆瓣秀中做一些简单设置</li>
<li>在你的page页面模板中加入 &lt;?php doubanshow();?&gt;</li>
<li>用这个page页的模板创建一个独立页面</li>
<li>如有问题，请通过Twitter联系我 @<a href="http://twitter.com/robb">robb</a></li>
</ol>

**Using Examples:**

    <div class="content">
                <?php the_content(); ?>
        <?php doubanshow(); ?>
                <div class="fixed"></div>
        </div>

**Custom CSS:**

    <style type="text/css">
    .doubanshow_profile{
    width:600px;
    margin-bottom:80px;
    }
    .doubanshow_profile_icon{
    margin-left:200px;
    float:left;
    }
    .doubanshow_profile_signature{
    margin:15px 0 0 50px;
    float:left;
    }
    .collection, .recommendations{
    width:600px;
    display:block;
    float:left;
    }
    .recommendations li{
    list-style-type:none;
    margin-left:30px;
    }
    .collection_list {
    display:block;
    float:left;
    width:100px;
    height:150px;
    line-height:90px;
    text-align:center;
    }
    .collection_list_img {
    border:0px;}
    .doubanshow_power_by{
    width:600px;
    text-align:center;
    }
    </style>

== Screenshots ==

1. WP-DoubanShow.
== Changelog ==

****

    VERSION DATE       TYPE   CHANGES
    1.0     2009/08/28 NEW    Added Russian language support.
    1.0     2009/08/25 NEW    Release.