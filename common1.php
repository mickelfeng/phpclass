<?php
function foo(&$var)
{
    $var++;
}

$a=5;
foo($a);
echo $a;

function genTree5($items) {
    foreach ($items as $item)
        $items[$item['pid']]['son'][$item['id']] = &$items[$item['id']];
    return isset($items[0]['son']) ? $items[0]['son'] : array();
}


function genTree9($items) {
    $tree = array(); //格式化好的树
    foreach ($items as $item)
        if (isset($items[$item['pid']]))
            $items[$item['pid']]['son'][] = &$items[$item['id']];
        else
            $tree[] = &$items[$item['id']];
    return $tree;
}
/*
mysql_connect("localhost","root","putclub");
mysql_select_db("ptscan");
$resource=mysql_query("select id,pid,catalog as name from ts_books_catalog");

while($row=mysql_fetch_assoc($resource)){
    $items[$row['id']]=$row;
}
*/
//print_r($items);
//print_r(genTree9($items));


function get_img($html)
{
    preg_match_all('/<img.*?src=[\'|"]?(\S*?)[\'|"|\s]/is', $html, $m);
    return $m[1];
} 

function save_img_curl($avatar_url)
{
    $res = curl_init($avatar_url);
    ob_start();
    curl_exec($res);
    $avatar_file = ob_get_contents();
    ob_end_clean();
    $info = curl_getinfo($res);
    curl_close($res);
    echo $info['total_time'] . "\n"; 
    // save avatar
    $up_dir = "./img/";
    if (!is_dir($up_dir))
    {
        @mkdir($up_dir, 0777);
    } 
    $avatar_name = time() . '.jpg';
    $handle = fopen($up_dir . $avatar_name, 'a');
    fwrite($handle, $avatar_file);
    fclose($handle);
    return;
} 
// 注意：我这里直接用.jpg作为扩展名了，实际上要根据$info['content-type']来决定。
// readfile方法
function save_img_curl($avatar_url)
{
    $time_start = time();
    ob_start();
    readfile($avatar_url);
    $avatar_file = ob_get_contents();
    ob_end_clean();
    $time_end = time();
    echo "\n" . $time_end - $time_start . "\n";
} 

/**
 * 判断浏览器名称和版本
 */
function get_user_browser()
{
    if (empty($_SERVER['HTTP_USER_AGENT']))
    {
        return '';
    }

    $agent       = $_SERVER['HTTP_USER_AGENT'];
    $browser     = '';
    $browser_ver = '';

    if (preg_match('/MSIE\s([^\s|;]+)/i', $agent, $regs))
    {
        $browser     = 'Internet Explorer';
        $browser_ver = $regs[1];
    }
    elseif (preg_match('/FireFox\/([^\s]+)/i', $agent, $regs))
    {
        $browser     = 'FireFox';
        $browser_ver = $regs[1];
    }
    elseif (preg_match('/Maxthon/i', $agent, $regs))
    {
        $browser     = '(Internet Explorer ' .$browser_ver. ') Maxthon';
        $browser_ver = '';
    }
    elseif (preg_match('/Opera[\s|\/]([^\s]+)/i', $agent, $regs))
    {
        $browser     = 'Opera';
        $browser_ver = $regs[1];
    }
    elseif (preg_match('/OmniWeb\/(v*)([^\s|;]+)/i', $agent, $regs))
    {
        $browser     = 'OmniWeb';
        $browser_ver = $regs[2];
    }
    elseif (preg_match('/Netscape([\d]*)\/([^\s]+)/i', $agent, $regs))
    {
        $browser     = 'Netscape';
        $browser_ver = $regs[2];
    }
    elseif (preg_match('/safari\/([^\s]+)/i', $agent, $regs))
    {
        $browser     = 'Safari';
        $browser_ver = $regs[1];
    }
    elseif (preg_match('/NetCaptor\s([^\s|;]+)/i', $agent, $regs))
    {
        $browser     = '(Internet Explorer ' .$browser_ver. ') NetCaptor';
        $browser_ver = $regs[1];
    }
    elseif (preg_match('/Lynx\/([^\s]+)/i', $agent, $regs))
    {
        $browser     = 'Lynx';
        $browser_ver = $regs[1];
    }

    if (!empty($browser))
    {
       return addslashes($browser . ' ' . $browser_ver);
    }
    else
    {
        return 'Unknow browser';
    }
}
/*几种防止sql注入的方法*/
function inject_check($sql_str) { 
    return eregi('select|insert|and|or|update|delete|\'|\/\*|\*|\.\.\/|\.\/|union|into|load_file|outfile', $sql_str);
} 
 
function verify_id($id=null) { 
    if(!$id) {
        exit('没有提交参数！'); 
    } elseif(inject_check($id)) { 
        exit('提交的参数非法！');
    } elseif(!is_numeric($id)) { 
        exit('提交的参数非法！'); 
    } 
    $id = intval($id); 
     
    return $id; 
} 
 

function str_check( $str ) { 
    if(!get_magic_quotes_gpc()) { 
        $str = addslashes($str); // 进行过滤 
    } 
    $str = str_replace("_", "\_", $str); 
    $str = str_replace("%", "\%", $str); 
     
   return $str; 
} 
 

function post_check($post) { 
    if(!get_magic_quotes_gpc()) { 
        $post = addslashes($post);
    } 
    $post = str_replace("_", "\_", $post); 
    $post = str_replace("%", "\%", $post); 
    $post = nl2br($post); 
    $post = htmlspecialchars($post); 
     
    return $post; 
}
/*防止Xss攻击简单方法
http://www.ibm.com/developerworks/cn/opensource/os-cn-php-xss/
*/
function removeXss($s){
 //清空空格字符
 $s = trim($s);
 //过滤html标签
 $s = strip_tags($s);
 //将文本中的内容转换为html实体
 $s = htmlspecialchars($s);
 //加入字符转义
 $s = addslashes($s);
 return $s; 
}
?>
