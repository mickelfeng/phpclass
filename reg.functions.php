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
