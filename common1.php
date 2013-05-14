<?php
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

?>
