<?php

defined('TC_CMS') or exit('Access Denied');

/**
 * TC_CMS
 * =======================================================
 * 版权所有 (C) 2010-2020 www.teamcen.com，并保留所有权利。
 * 网站地址: http://www.teamcen.com
 * @version :    v1.0
 * =======================================================
 */

class safe {
	const getfilter="'|(and|or)\\b.+?(>|<|=|in|like)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
	const postfilter="\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";
	const cookiefilter="\\b(and|or)\\b.{1,6}?(=|>|<|\\bin\\b|\\blike\\b)|\\/\\*.+?\\*\\/|<\\s*script\\b|\\bEXEC\\b|UNION.+?SELECT|UPDATE.+?SET|INSERT\\s+INTO.+?VALUES|(SELECT|DELETE).+?FROM|(CREATE|ALTER|DROP|TRUNCATE)\\s+(TABLE|DATABASE)";

	public function __construct() {

	}

	public function initLogHacker() {
		
		foreach($_GET as $key=>$value){
			$this->StopAttack($key,$value,self::getfilter);
			$_GET[$key] = $this->safe_replace($value);
			$_GET[$key] = $this->remove_xss($_GET[$key]);
		}
		
		foreach($_COOKIE as $key=>$value){
			$this->StopAttack($key,$value,self::cookiefilter);
		}
		
		foreach($_POST as $key=>$value){
			if (!is_array($value)) {
				$_POST[$key] = $this->trim_script($value);
			}
			$this->StopAttack($key,$value,self::postfilter);
		}
	}
	
	private function StopAttack($StrFiltKey,$StrFiltValue,$ArrFiltReq){
		if(is_array($StrFiltValue)) {
			$StrFiltValue=implode($StrFiltValue);
		}     
		if (preg_match("/".$ArrFiltReq."/is",$StrFiltValue)==1){
			$attackStr = "<br><br>SubmitIP:".$_SERVER["REMOTE_ADDR"]."<br>SubmitTime:".strftime("%Y-%m-%d %H:%M:%S")."<br>SubmitPage:".$_SERVER["PHP_SELF"]."<br>SubmitMethod:".$_SERVER["REQUEST_METHOD"]."<br>SubmitParams:".$StrFiltKey."<br>SubmitData:".$StrFiltValue;
			exit("Illegal operation:" . $attackStr);
		}
	}
	
	/**
	 * 安全过滤函数
	 *
	 * @param $string
	 * @return string
	 */
	private function safe_replace($string) {
		$string = str_replace('%20', '', $string);
		$string = str_replace('%27', '', $string);
		$string = str_replace('%2527', '', $string);
		$string = str_replace('*', '', $string);
		$string = str_replace('"', '&quot;', $string);
		$string = str_replace("'", '', $string);
		$string = str_replace('"', '', $string);
		$string = str_replace(';', '', $string);
		$string = str_replace('<', '&lt;', $string);
		$string = str_replace('>', '&gt;', $string);
		$string = str_replace("{", '', $string);
		$string = str_replace('}', '', $string);
		$string = str_replace('\\', '', $string);
		$string = str_replace('or', '', $string);
		$string = str_replace('insert', '', $string);
		$string = str_replace('update', '', $string);
		$string = str_replace('delete', '', $string);
		$string = str_replace('and', '', $string);
		$string = str_replace('union', '', $string);
		$string = str_replace('load_file', '', $string);
		$string = str_replace('outfil', '', $string);
		$string = str_replace('TRUNCATE', '', $string);
		return $string;
	}
	
	/**
	 * 转义 javascript 代码标记
	 *
	 * @param $str
	 * @return mixed
	 */
	private function trim_script($str) {
		if (is_array($str)) {
			foreach ($str as $key => $val) {
				$str[$key] = trim_script($val);
			}
		} else {
			$str = preg_replace('/\<([\/]?)script([^\>]*?)\>/si', '&lt;\\1script\\2&gt;', $str);
			$str = preg_replace('/\<([\/]?)iframe([^\>]*?)\>/si', '&lt;\\1iframe\\2&gt;', $str);
			$str = preg_replace('/\<([\/]?)frame([^\>]*?)\>/si', '&lt;\\1frame\\2&gt;', $str);
			$str = preg_replace('/]]\>/si', ']] >', $str);
		}
		return $str;
	}
	
	/**
	 * 防止XSS攻击,用在表单textarea中内容过滤较多
	 *
	 * @param $str
	 * @return mixed
	 */
	private function remove_xss($val) {
		$val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);
		$search = 'abcdefghijklmnopqrstuvwxyz';
		$search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$search .= '1234567890!@#$%^&*()';
		$search .= '~`";:?+/={}[]-_|\'\\';
		for ($i = 0; $i < strlen($search); $i++) {
			$val = preg_replace('/(&#[xX]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $val); // with a ;
			$val = preg_replace('/(&#0{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $val); // with a ;
		}

		$ra1 = array(
            'javascript',
            'vbscript',
            'expression',
            'applet',
            'meta',
            'xml',
            'blink',
            'link',
            'style',
            'script',
            'embed',
            'object',
            'iframe',
            'frame',
            'frameset',
            'ilayer',
            'layer',
            'bgsound',
            'title',
            'base'
            );
            $ra2 = array(
            'onabort',
            'onactivate',
            'onafterprint',
            'onafterupdate',
            'onbeforeactivate',
            'onbeforecopy',
            'onbeforecut',
            'onbeforedeactivate',
            'onbeforeeditfocus',
            'onbeforepaste',
            'onbeforeprint',
            'onbeforeunload',
            'onbeforeupdate',
            'onblur',
            'onbounce',
            'oncellchange',
            'onchange',
            'onclick',
            'oncontextmenu',
            'oncontrolselect',
            'oncopy',
            'oncut',
            'ondataavailable',
            'ondatasetchanged',
            'ondatasetcomplete',
            'ondblclick',
            'ondeactivate',
            'ondrag',
            'ondragend',
            'ondragenter',
            'ondragleave',
            'ondragover',
            'ondragstart',
            'ondrop',
            'onerror',
            'onerrorupdate',
            'onfilterchange',
            'onfinish',
            'onfocus',
            'onfocusin',
            'onfocusout',
            'onhelp',
            'onkeydown',
            'onkeypress',
            'onkeyup',
            'onlayoutcomplete',
            'onload',
            'onlosecapture',
            'onmousedown',
            'onmouseenter',
            'onmouseleave',
            'onmousemove',
            'onmouseout',
            'onmouseover',
            'onmouseup',
            'onmousewheel',
            'onmove',
            'onmoveend',
            'onmovestart',
            'onpaste',
            'onpropertychange',
            'onreadystatechange',
            'onreset',
            'onresize',
            'onresizeend',
            'onresizestart',
            'onrowenter',
            'onrowexit',
            'onrowsdelete',
            'onrowsinserted',
            'onscroll',
            'onselect',
            'onselectionchange',
            'onselectstart',
            'onstart',
            'onstop',
            'onsubmit',
            'onunload'
            );
            $ra = array_merge($ra1, $ra2);

            $found = true; // keep replacing as long as the previous round replaced something
            while ($found == true) {
            	$val_before = $val;
            	for ($i = 0; $i < sizeof($ra); $i++) {
            		$pattern = '/';
            		for ($j = 0; $j < strlen($ra[$i]); $j++) {
            			if ($j > 0) {
            				$pattern .= '(';
            				$pattern .= '(&#[xX]0{0,8}([9ab]);)';
            				$pattern .= '|';
            				$pattern .= '|(&#0{0,8}([9|10|13]);)';
            				$pattern .= ')*';
            			}
            			$pattern .= $ra[$i][$j];
            		}
            		$pattern .= '/i';
            		$replacement = substr($ra[$i], 0, 2) . '<x>' . substr($ra[$i], 2); // add in <> to nerf the tag
            		$val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
            		if ($val_before == $val) {
            			// no replacements were made, so exit the loop
            			$found = false;
            		}
            	}
            }
            return $val;
	}
}

?>