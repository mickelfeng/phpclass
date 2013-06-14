// 获得所有a标签 , 
  function str_geta( $str ) {
		if ( preg_match_all( '@<a.+?(href\s*=\s*[\"\']?([^\'\"]*)[\"\']?)[^>]*>([\s\S]*?)</a>@i' , $str , $match ) ) {
			return $match;
		}
	}

	// 获得所有link标签 , 
	function str_getlink( $str ) {
		if ( preg_match_all( '@<link.+?(href\s*=\s*[\"\']?([^\'\"]*)[\"\']?)[^>]*>@i' , $str , $match ) ) {
			return $match;
		}
	}

	// 获得所有script标签 , 
	function str_getscript( $str ) {
		if ( preg_match_all( '@<script.+?(src\s*=\s*[\"\']?([^\'\"]*)[\"\']?)[^>]*>[^<]*</script>@i' , $str , $match ) ) {
			return $match;
		}
	}

	// 获得所有img标签 , 
	function str_getimg( $str ) {
		if ( preg_match_all( '@<img.+?(src\s*=\s*[\"\']?([^\'\"]*)[\"\']?)[^>]*>@i' , $str , $match ) ) {
			return $match;
		}
	}
    
    var_dump(str_getimg(file_get_contents("http://www.putclub.com/")));
//php使用正则表达式获取图片url

header("Content-type:text/html;charset=utf-8");
$str = '<p><img src="images/11111111.jpg" alt="美女" /></p>';
$pattern = "/[img|IMG].*?src=['|\"](.*?(?:[.gif|.jpg]))['|\"].*?[\/]?>/";
preg_match_all($pattern,$str,$match);
echo "<pre/>";
print_r($match);

<?php
$str ='<a id="top8" href="http://abc.com/song/A.htm" class="p14" target="_top">歌曲列表</a><br><a target="_blank" id="bp" href="http://bca.com/list/bangping.html" class="p14">中文金曲榜</a><br><td nowrap="nowrap">&nbsp;<a id="top19" href="qingyinyue.html" class="p14" target="_top">轻音乐</a></td>';
$str = $str ."<iframe src=\"/info/public/bipin.shtml\" id=\"leitai\" name=\"leitai\" frameborder=\"0\" scrolling=\"no\" width=\"100%\" height=\"307px;\"></iframe>";
//链接地址+标题(href必须带双引号)
$pat ='/<a(.*?)href="(.*?)"(.*?)>(.*?)<\/a>/i';
preg_match_all($pat, $str, $m);
print_r($m[2]);
print_r($m[4]);
for($i=0;$i<count($m[2]) ;$i++){
     echo '<li><a href="'.$_SERVER['PHP_SELF'].'?url='.$m[2][$i].'">'.$m[4][$i].'</a></li>';
}
echo "<hr />";
//仅链接地址(href必须带双引号)
preg_match_all('/(?<=href=")[\w\d\.:\/]*/',$str,$m);
print_r($m);
echo "<hr />";
//链接地址+标题(通用)
preg_match_all('/<a.*?(?: |\\t|\\r|\\n)?href=[\'"]?(.+?)[\'"]?(?:(?: |\\t|\\r|\\n)+.*?)?>(.+?)<\/a.*?>/sim',$str,$m);
print_r($m[1]);
print_r($m[2]);
echo "<hr />";
//iframe地址(通用)
preg_match_all('/<iframe.*?(?: |\\t|\\r|\\n)?src=[\'"]?(.+?)[\'"]?(?:(?: |\\t|\\r|\\n)+.*?)?>(.*?)<\/iframe.*?>/sim',$str,$m);
print_r($m[1]);
?>
