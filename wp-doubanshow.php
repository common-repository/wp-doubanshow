<?php
/*
Plugin Name: WP-DoubanShow
Plugin URI: http://www.robb.com.cn/plugins/
Description: Show your douban's collections and recommendations in a page, just put <code>&lt;?php doubanshow(); ?&gt;</code> in your template.DEMO: <a href="http://www.robb.com.cn/douban" target="_blank">robb's douban</a>. 
Version: 1.0
Author: Robb(Liu Bo)
Author URI: http://www.robb.com.cn
*/


/*  
	Copyright 2009  Robb(Liu Bo)  (email : robbliu@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software.
*/
load_plugin_textdomain('wp-doubanshow', "/wp-content/plugins/wp-doubanshow/languages/");
//语言项
$lang = array(
            'movie' => array(
                'wish' => __('Wants to see', 'wp-doubanshow'),
                'watched' => __('Has seen', 'wp-doubanshow')
                ),
            'book' => array(
                'wish' => __('Wants to read', 'wp-doubanshow'),
                'reading' => __('Is reading', 'wp-doubanshow'),
                'read' => __('Has read', 'wp-doubanshow')
                ),
            'music' => array(
                'wish' => __('Wants to listen', 'wp-doubanshow'),
                'listening' => __('Is listening', 'wp-doubanshow'),
                'listened' => __('Has listened', 'wp-doubanshow')
                )
    );

//兼容低版本php的json解析函数
function jsondecode($result)
{
    if(!class_exists("Services_JSON")){
		require_once("json.php");
	}
	$json = new Services_JSON();
	$result =  $json->decode($result);
    $arr = obj2Arr($result);
    return $arr;
}
//对象转换为数组
function obj2Arr($obj)
{
    $result = NULL;
    if(!is_array($obj))
    {
        if($var = get_object_vars($obj))
        {
            foreach($var as $key => $value)
            {
                $result[$key] = obj2Arr($value);
            }
        }
        else
        {
            return $obj;
        }
    }
    else
    {
        foreach($obj as $key => $value)
        {
            $result[$key] = obj2Arr($value);
        }
    }
    return $result;
}


//调取用户的个人资料（暂时没用）
function profile($userID = '',$apikey = '')
{
    $url = 'http://api.douban.com/people/'.$userID.'?alt=json';
    if (!empty($apikey))
    {
        $url .= '&apikey='.$apikey;
    }
    $result = file_get_contents($url);
    if (function_exists("json_decode")) {
        $result = json_decode($result,true);
    }
    else
    {
        $result = jsondecode($result);
    }
    $content = '<div class="doubanshow_profile"><div class="doubanshow_profile_icon"><a href="'.$result['link'][1]['@href'].'" title="'.$result['title']['$t'].'" target="_blank"><img class="doubanshow_profile_img" src="'.$result['link'][2]['@href'].'" alt="'.$result['title']['$t'].'"></a></div>';
    $content .= '<div class="doubanshow_profile_signature"><a href="'.$result['link'][1]['@href'].'" target="_blank">'.$result['title']['$t'].'</a></div></div>';
    return $content;
}

//调取用户的收藏
function collection($userID = '',$apikey = '',$type = 'movie',$num = 5,$status = 'wish')
{
    global $lang;
    $url = 'http://api.douban.com/people/'.$userID.'/collection?cat='.$type.'&max-results='.$num.'&status='.$status.'&alt=json';
    if (!empty($apikey))
    {
        $url .= '&apikey='.$apikey;
    }
    $result = file_get_contents($url);
    if (function_exists("json_decode")) {
        $result = json_decode($result,true);
    }
    else
    {
        $result = jsondecode($result);
    }
    $content = '<div class="collection"><div class="collection_list">'.$lang[$type][$status].'</div>';
    foreach ($result['entry'] as $val)
    {
        $content .= '<div class="collection_list"><a href="'.$val['db:subject']['link'][1]['@href'].'" title="'.$val['db:subject']['title']['$t'].'" target="_blank"><img class="collection_list_img" src="'.$val['db:subject']['link'][2]['@href'].'" alt="'.$val['db:subject']['title']['$t'].'"></a></div>';
    }
    $content .= '</div>';
    return $content;
}

//调取用户的评论
function recommendations($userID = '',$apikey = '',$num = 5,$start = 1)
{
    $url = 'http://api.douban.com/people/'.$userID.'/recommendations?start-index='.$start.'&max-results='.$num.'&alt=json';
    if (!empty($apikey))
    {
        $url .= '&apikey='.$apikey;
    }
    $result = file_get_contents($url);
    if (function_exists("json_decode")) {
        $result = json_decode($result,true);
    }
    else
    {
        $result = jsondecode($result);
    }
    $content = '<div class="recommendations"><h2></h2><ul>';
    foreach ($result['entry'] as $val)
    {
        $content .= '<li>'.$val['content']['$t'].'</li>';
    }
    $content .= '</ul></div>';
    return $content;
}

//更新豆瓣秀资料
function update_doubanshow()
{
    $wp_doubanshow_option = get_option("wp_doubanshow_option");
    $userID = $wp_doubanshow_option['userid'];
    $apikey = $wp_doubanshow_option['apikey'];
    $profile = $wp_doubanshow_option['profile'];
    $collection = $wp_doubanshow_option['collection'];
    $recommendations = $wp_doubanshow_option['recommendations'];
    $powerby = $wp_doubanshow_option['powerby'];
    $content = '';
    if ($profile == 1)
    {
        $content .= profile($userID,$apikey);
    }
    foreach ($collection as $cat => $cat_arr)
    {
        foreach ($cat_arr as $key => $val)
        {
            if ($val['chk'] == 1)
            {
                $content .= collection($userID,$apikey,$cat,$val['num'],$key);
            }
        }
    }
    if ($recommendations['chk'] == 1)
    {
        $content .= recommendations($userID,$apikey,$recommendations['num']);
    }
    if ($powerby == 1)
    {
        $content .= '<div class="doubanshow_power_by"><p><small>Power by <a href="http://www.robb.com.cn/2009/08/wp-doubanshow/">DoubanShow</a></small></p></div>';
    }
    update_option('wp_doubanshow_content',$content);
}

//创建自定义的更新豆瓣秀的事件，调用更新豆瓣秀的函数
add_action('update_doubanshow_event', 'update_doubanshow');

//定义任务，调用更新豆瓣秀的事件
if (!wp_next_scheduled('update_doubanshow_event')) {
	wp_schedule_event( time(), 'hourly', 'update_doubanshow_event' );
}

//当插件被停止时候，移除更新豆瓣秀的事件
function update_doubanshow_deactivation(){
	wp_clear_scheduled_hook('update_doubanshow_event');
}
register_deactivation_hook(basename(__FILE__),'update_doubanshow_deactivation');

//获取豆瓣秀数据
function get_doubanshow()
{
	$content = get_option('wp_doubanshow_content');	//从数据库中获取豆瓣秀数据
	
	if(!$content){	//如果是第一次使用，把豆瓣秀数据写入数据库
		update_doubanshow();
		$content = get_option('wp_doubanshow_content');	//重新获取
	}
	return $content;//输出豆瓣秀数据
}

//输出豆瓣秀数据
function doubanshow(){
	$output = get_doubanshow();
	echo $output;
}

function wp_doubanshow_options()
{
    global $lang;
	$message = __('Update Successful', 'wp-doubanshow');
	if($_POST['update_doubanshow_option'])
    {
		$wp_doubanshow_option_saved = get_option("wp_doubanshow_option");
        $wp_doubanshow_option = array (
			"userid" 	        => $_POST['userid'],
            "apikey" 	        => $_POST['apikey'],
            "profile" 	        => $_POST['profile'],
			"collection"		=> $_POST['collection'],
			"recommendations"	=> $_POST['recommendations'],
            "powerby"	=> $_POST['powerby']
		);
		if ($wp_doubanshow_option_saved != $wp_doubanshow_option)
        {
			if(!update_option("wp_doubanshow_option",$wp_doubanshow_option))
            {
				$message = __('Update Failed', 'wp-doubanshow');
            }
        }
		update_doubanshow();
		
		echo '<div class="updated"><strong><p>'. $message . '</p></strong></div>';
	}
    $wp_doubanshow_option = get_option("wp_doubanshow_option");
    if(empty($wp_doubanshow_option))
    {
        $userID = '';
        $apikey = '';
        $profile = '';
        $collection = array();
        foreach ($lang as $cat => $cat_arr)
        {
            foreach ($cat_arr as $key => $val)
            {
               $collection[$cat][$key]['num'] = 5;
            }
        }
        $recommendations['num'] = 5;
        $powerby = '';
    }
    else
    {
        $userID = $wp_doubanshow_option['userid'];
        $apikey = $wp_doubanshow_option['apikey'];
        $profile = $wp_doubanshow_option['profile'];
        $collection = $wp_doubanshow_option['collection'];
        $recommendations = $wp_doubanshow_option['recommendations'];
        foreach ($collection as $cat => $cat_arr)
        {
            foreach ($cat_arr as $key => $val)
            {
                if (empty($val['num']))
                {
                    $collection[$cat][$key]['num'] = 5;
                }
            }
        }
        if (empty($recommendations['num']))
        {
            $recommendations['num'] = 5;
        }
        $powerby = $wp_doubanshow_option['powerby'];
    }
    
?>
<div class=wrap>
	<form method="post" action="">
		<h2><?php _e('DoubanShow Options', 'wp-doubanshow'); ?></h2>
		<fieldset name="wp_basic_options"  class="options">
		<table>
            <tr>
                <td valign="top" align="right"><?php _e('Douban ID:', 'wp-doubanshow'); ?></td>
				<td><input type="text" name="userid" value="<?php echo $userID;  ?>" /> <?php _e('Enter your Douban ID.', 'wp-doubanshow'); ?></td>
			</tr>
			<tr>
                <td valign="top" align="right"><?php _e('API key:', 'wp-doubanshow'); ?></td>
				<td><input type="text" name="apikey" value="<?php echo $apikey;  ?>" /> <?php _e("Non-essential.<a href='http://www.douban.com/service/apikey/apply' target='_blank'>Apply</a>", 'wp-doubanshow'); ?></td>
			</tr>
            <tr>
                <td valign="top" align="right"><?php _e('Show my profile:', 'wp-doubanshow'); ?></td>
				<td><input  type="checkbox" value="1" <?php if($profile==1){ echo 'checked'; } ?> name="profile" /></td>
			</tr>
			<tr>
                <td valign="top" align="right"><?php _e('Show the movies I want to see:', 'wp-doubanshow'); ?></td>
                <td><input  type="checkbox" value="1" <?php if($collection['movie']['wish']['chk']==1){ echo 'checked'; } ?> name="collection[movie][wish][chk]" /> <?php _e('Number:', 'wp-doubanshow'); ?><input type="text" name="collection[movie][wish][num]" value="<?php echo $collection['movie']['wish']['num'] ?>" /></td>
            </tr>
			<tr>
                <td valign="top" align="right"><?php _e('Show the movies I have seen:', 'wp-doubanshow'); ?></td>
                <td><input  type="checkbox" value="1" <?php if($collection['movie']['watched']['chk']==1){ echo 'checked'; } ?> name="collection[movie][watched][chk]" /> <?php _e('Number:', 'wp-doubanshow'); ?><input type="text" name="collection[movie][watched][num]" value="<?php echo $collection['movie']['watched']['num'] ?>" /></td>
            </tr>
			<tr>
                <td valign="top" align="right"><?php _e('Show the books I want to read:', 'wp-doubanshow'); ?></td>
                <td><input  type="checkbox" value="1" <?php if($collection['book']['wish']['chk']==1){ echo 'checked'; } ?> name="collection[book][wish][chk]" /> <?php _e('Number:', 'wp-doubanshow'); ?><input type="text" name="collection[book][wish][num]" value="<?php echo $collection['book']['wish']['num'] ?>" /></td>
            </tr>
			<tr>
                <td valign="top" align="right"><?php _e('Show the books I am reading:', 'wp-doubanshow'); ?></td>
                <td><input  type="checkbox" value="1" <?php if($collection['book']['reading']['chk']==1){ echo 'checked'; } ?> name="collection[book][reading][chk]" /> <?php _e('Number:', 'wp-doubanshow'); ?><input type="text" name="collection[book][reading][num]" value="<?php echo $collection['book']['reading']['num'] ?>" /></td>
            </tr>
			<tr>
                <td valign="top" align="right"><?php _e('Show the movies I have read:', 'wp-doubanshow'); ?></td>
                <td><input  type="checkbox" value="1" <?php if($collection['book']['read']['chk']==1){ echo 'checked'; } ?> name="collection[book][read][chk]" /> <?php _e('Number:', 'wp-doubanshow'); ?><input type="text" name="collection[book][read][num]" value="<?php echo $collection['book']['read']['num'] ?>" /></td>
            </tr>
			<tr>
                <td valign="top" align="right"><?php _e('Show the music I want to listen:', 'wp-doubanshow'); ?></td>
                <td><input  type="checkbox" value="1" <?php if($collection['music']['wish']['chk']==1){ echo 'checked'; } ?> name="collection[music][wish][chk]" /> <?php _e('Number:', 'wp-doubanshow'); ?><input type="text" name="collection[music][wish][num]" value="<?php echo $collection['music']['wish']['num'] ?>" /></td>
            </tr>
			<tr>
                <td valign="top" align="right"><?php _e('Show the music I am listening:', 'wp-doubanshow'); ?></td>
                <td><input  type="checkbox" value="1" <?php if($collection['music']['listening']['chk']==1){ echo 'checked'; } ?> name="collection[music][listening][chk]" /> <?php _e('Number:', 'wp-doubanshow'); ?><input type="text" name="collection[music][listening][num]" value="<?php echo $collection['music']['listening']['num'] ?>" /></td>
            </tr>
			<tr>
                <td valign="top" align="right"><?php _e('Show the music I have listened:', 'wp-doubanshow'); ?></td>
                <td><input  type="checkbox" value="1" <?php if($collection['music']['listened']['chk']==1){ echo 'checked'; } ?> name="collection[music][listened][chk]" /> <?php _e('Number:', 'wp-doubanshow'); ?><input type="text" name="collection[music][listened][num]" value="<?php echo $collection['music']['listened']['num'] ?>" /></td>
            </tr>
			<tr>
                <td valign="top" align="right"><?php _e('Show my recommendation:', 'wp-doubanshow'); ?></td>
                <td><input  type="checkbox" value="1" <?php if($recommendations['chk']==1){ echo 'checked'; } ?> name="recommendations[chk]" /> <?php _e('Number:', 'wp-doubanshow'); ?><input type="text" name="recommendations[num]" value="<?php echo $recommendations['num'] ?>" /></td>
            </tr>
            <tr>
                <td valign="top" align="right"><?php _e('Display Power by DoubanShow?', 'wp-doubanshow'); ?></td>
				<td><input  type="checkbox" value="1" <?php if($powerby==1){ echo 'checked'; } ?> name="powerby" /></td>
			</tr>
		</table>		
			
		</fieldset>
		<p class="submit"><input type="submit" name="update_doubanshow_option" value="Update Options &raquo;" /></p>
	</form>
</div>
<?php
}

function wp_doubanshow_options_admin(){
	add_options_page('wp_doubanshow', __('DoubanShow', 'wp-doubanshow'), 5,  __FILE__, 'wp_doubanshow_options');
}

add_action('admin_menu', 'wp_doubanshow_options_admin');
?>