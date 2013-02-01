<?php
/**
 * 将一个URL转换为完整URL
 * $srcurl = '/guestbook.php';
 * $baseurl = 'http://www.msphome.cn/index.php/ddd.html';
 * echo format_url($srcurl, $baseurl);
 */
function format_url($srcurl, $baseurl)
{
    $srcinfo = parse_url($srcurl);
    if (isset($srcinfo['scheme']))
    {
        return $srcurl;
    } 
    $baseinfo = parse_url($baseurl);
    $url = $baseinfo['scheme'] . '://' . $baseinfo['host'];
    if (substr($srcinfo['path'], 0, 1) == '/')
    {
        $path = $srcinfo['path'];
    } 
    else
    {
        $path = dirname($baseinfo['path']) . '/' . $srcinfo['path'];
    } 
    $rst = array();
    $path_array = explode('/', $path);
    if (!$path_array[0])
    {
        $rst[] = '';
    } 
    foreach ($path_array AS $key => $dir)
    {
        if ($dir == '..')
        {
            if (end($rst) == '..')
            {
                $rst[] = '..';
            } elseif (!array_pop($rst))
            {
                $rst[] = '..';
            } 
        } elseif ($dir && $dir != '.')
        {
            $rst[] = $dir;
        } 
    } 
    if (!end($path_array))
    {
        $rst[] = '';
    } 
    $url .= implode('/', $rst);
    return str_replace('\\', '/', $url);
} 



// 相对路径转化成绝对路径
function relative_to_absolute($content, $feed_url)
{
    preg_match('/(http|https|ftp):///', $feed_url, $protocol);
    $server_url = preg_replace("/(http|https|ftp|news):///", "", $feed_url); 
    // 开源OSPhP.COM.CN
    $server_url = preg_replace("//.*/", "", $server_url);
    if ($server_url == '')
    {
        return $content;
    } 
    if (isset($protocol[0]))
    { 
        // 开源代码OSPhP.COm.CN
        $new_content = preg_replace('/href="//', 'href="' . $protocol[0] . $server_url . '/', $content);
        $new_content = preg_replace('/src="//', 'src="' . $protocol[0] . $server_url . '/', $new_content); //开源OSPhP.COM.CN 
    } 
    else
    {
        $new_content = $content;
    } 
    return $new_content;
} 
/**
 * 直接记录日志
 * 
 * @param  $ <type> $lvevel
 * @param string $level 
 */
function Write($msg, $level)
{
    $arr_level = explode(',', YUC_LOG_TYPE);
    if (in_array($level, $arr_level))
    {
        $record = date('Y-m-d H:m:s') . " >>> " . number_format(microtime(true), 5, ".", "") . ' ' . " : " . $level . "\t" . $msg;
        $base = M_PRO_DIR . "/Log";
        $dest = $base . "/" . date("YmdH", time()) . 'log.php';
        if (!file_exists($dest))
        {
            @mkdir($base, 0777, true);
            @file_put_contents($dest, "<?php die('Access Defined!');?>\r\n", FILE_APPEND);
        } 
        if (file_exists($dest))
        {
            @file_put_contents($dest, $record . "\r\n", FILE_APPEND);
        } 
    } 
} 

function get_user_agent()
{
    if (strpos($_SERVER["HTTP_USER_AGENT"], "MSIE 8.0"))
        return "Internet Explorer 8.0";
    else if (strpos($_SERVER["HTTP_USER_AGENT"], "MSIE 7.0"))
        return "Internet Explorer 7.0";
    else if (strpos($_SERVER["HTTP_USER_AGENT"], "MSIE 6.0"))
        return "Internet Explorer 6.0";
    else if (strpos($_SERVER["HTTP_USER_AGENT"], "Firefox/3"))
        return "Firefox 3";
    else if (strpos($_SERVER["HTTP_USER_AGENT"], "Firefox/2"))
        return "Firefox 2";
    else if (strpos($_SERVER["HTTP_USER_AGENT"], "Chrome"))
        return "Google Chrome";
    else if (strpos($_SERVER["HTTP_USER_AGENT"], "Safari"))
        return "Safari";
    else if (strpos($_SERVER["HTTP_USER_AGENT"], "Opera"))
        return "Opera";
    else return $_SERVER["HTTP_USER_AGENT"];
} 
/**
 * * 判断浏览器语言
 */
function get_user_agent_lang()
{
    $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 4); //只取前4位，这样只判断最优先的语言。如果取前5位，可能出现en,zh的情况，影响判断。  
    if (preg_match("/zh-c/i", $lang))
        return "简体中文";
    else if (preg_match("/zh/i", $lang))
        return "繁體中文";
    else if (preg_match("/en/i", $lang))
        return "English";
    else if (preg_match("/fr/i", $lang))
        return "French";
    else if (preg_match("/de/i", $lang))
        return "German";
    else if (preg_match("/jp/i", $lang))
        return "Japanese";
    else if (preg_match("/ko/i", $lang))
        return "Korean";
    else if (preg_match("/es/i", $lang))
        return "Spanish";
    else if (preg_match("/sv/i", $lang))
        return "Swedish";
    else return $_SERVER["HTTP_ACCEPT_LANGUAGE"];
} 
/**
 * 下面的函数可以用来过滤用户的输入，保证输入是XSS安全的。具体如何过滤，可以参看函数内部，也有注释。
 */
function RemoveXSS($val)
{ 
    // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
    // this prevents some character re-spacing such as <java\0script>
    // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
    $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val); 
    // straight replacements, the user should never need these since they're normal characters
    // this prevents like <IMG SRC=@avascript:alert('XSS')>
    $search = 'abcdefghijklmnopqrstuvwxyz';
    $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $search .= '1234567890!@#$%^&*()';
    $search .= '~`";:?+/={}[]-_|\'\\';
    for ($i = 0; $i < strlen($search); $i++)
    { 
        // ;? matches the ;, which is optional
        // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars
        // search for the hex values
        $val = preg_replace('/(&#[xX]0{0,8}' . dechex(ord($search[$i])) . ';?)/i', $search[$i], $val); // with a ;        
        // 0{0,7} matches '0' zero to seven times
        $val = preg_replace('/(&#0{0,8}' . ord($search[$i]) . ';?)/', $search[$i], $val); // with a ; 
    } 
    // now the only remaining whitespace attacks are \t, \n, and \r
    $ra1 = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
    $ra2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
    $ra = array_merge($ra1, $ra2);

    $found = true; // keep replacing as long as the previous round replaced something 
    while ($found == true)
    {
        $val_before = $val;
        for ($i = 0; $i < sizeof($ra); $i++)
        {
            $pattern = '/';
            for ($j = 0; $j < strlen($ra[$i]); $j++)
            {
                if ($j > 0)
                {
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
            if ($val_before == $val)
            { 
                // no replacements were made, so exit the loop
                $found = false;
            } 
        } 
    } 
    return $val;
} 

/**
 * 记录一条日志，会以以下三种方式依次尝试写日志。
 * - 向当前参数指定的文件写入日志。
 * - 尝试向php.ini中指定的error_log写内容。
 * - 向系统日志写内容，还是失败的话则返回false。
 * 
 * 不用每次调用时都指定logFile和dateFormat参数
 * 系统会自动记住上次指定的内容。
 * 
 * PHP5.0之后请确保已经设置好时区，否则可能会抛出一个错误。
 * example:
 * 
 * @code php
 * // 第一次调用，初始化日志，并写入第一条信息。
 * logg('init...', LOG_INFO, '/usr/log.txt', 'y-m-d');
 * // 写日志
 * logg('log msg', LOG_INFO);
 * @endcode 
 * @param string $message 日志内容
 * @param int $type 日志类型，参照syslog函数的参数
 * @param string $logFile 日志文件
 * @param string $dateFormat 日志的时间格式
 * @return bool 是否成功写入
 * @staticvar array $types 参数$type对应的描述信息。
 * @staticvar string $file 保存$logFile参数最后次传递的内容。
 * @staticvar string $format 保存$dateFormat参数最后传递的内容。
 * @link http://blog.830725.com/post/13.html
 */
function logg($message, $type, $logFile = null, $dateFormat = null)
{
    static $types = array(
        LOG_EMERG => 'EMERG',
        LOG_ALERT => 'ALERT',
        LOG_CRIT => 'CRITICAL',
        LOG_ERR => 'ERROR',
        LOG_WARNING => 'WARNING', 
        // windows下，以下这三个值是一样的
        LOG_NOTICE => 'NOTICE',
        LOG_DEBUG => 'DEBUG',
        LOG_INFO => 'INFO');
    static $file = null;
    static $format = 'Y-m-d H:i:s';
    if (!is_null($logFile))
    {
        $file = $logFile;
    } 
    if (!is_null($dateFormat))
    {
        $format = $dateFormat;
    } 
    /**
     * 格式化消息
     */
    $type = isset($types[$type]) ? $type : LOG_INFO;
    $msg = date($format) . ' [' . $types[$type] . '] ' . $message . PHP_EOL;
    if (error_log($msg, 3, $file))
    {
        return true;
    } 
    if (error_log($msg, 0))
    {
        return true;
    } 
    return syslog($type, $message);
} 

/**
 * 获取用户真实 IP
 */
function getIP()
{
    static $realip;
    if (isset($_SERVER))
    {
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
        {
            $realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } 
        else if (isset($_SERVER["HTTP_CLIENT_IP"]))
        {
            $realip = $_SERVER["HTTP_CLIENT_IP"];
        } 
        else
        {
            $realip = $_SERVER["REMOTE_ADDR"];
        } 
    } 
    else
    {
        if (getenv("HTTP_X_FORWARDED_FOR"))
        {
            $realip = getenv("HTTP_X_FORWARDED_FOR");
        } 
        else if (getenv("HTTP_CLIENT_IP"))
        {
            $realip = getenv("HTTP_CLIENT_IP");
        } 
        else
        {
            $realip = getenv("REMOTE_ADDR");
        } 
    } 

    return $realip;
} 

/**
 * 获取 IP  地理位置
 * 淘宝IP接口
 * 
 * @Return : array
 */
function getCity($ip)
{
    $url = "http://ip.taobao.com/service/getIpInfo.php?ip=" . $ip;
    $ip = json_decode(file_get_contents($url));
    if ((string)$ip -> code == '1')
    {
        return false;
    } 
    $data = (array)$ip -> data;
    return $data;
} 

/**
 * 彩色标签Tags
 * 在控制器加入获取标签列表的方法
 * //标签控制器查询标签表以获取标签列表   
 * function Tags($Module)
 * {
 *                 $Tag = M('Tag');
 *                 $map['module'] = $Module;
 *                 $Tagslist = $Tag -> where($map) -> field('id,name,count') -> order('count desc') -> limit('0,25') -> select();
 *                 $this -> assign('tags', $Tagslist);
 *                 $this -> display();
 * } 
 * 把标签列表输出在模板上 
 * <volist id="vo" name="tags" >   
 * <li><a href="{:U('/web','tag='.$vo['name']|urlencode)}">
 * <span style="font-size:{color:{$vo.id|rand_color}">&nbsp;&nbsp;{$vo.name}[{$vo.count}]</span>
 * </a></li>   
 * </volist>
 */

function rcolor()
{
    $rand = rand(0, 255); //随机获取0--255的数字   
    return sprintf("%02X", "$rand"); //输出十六进制的两个大写字母   
} 

function rand_color()
{
    return '#' . rcolor() . rcolor() . rcolor(); //六个字母   
} 

/**
 * 根据IP地址获取地理信息
 */

function getIpLocation($ip = null)
{
    $ip = is_null($ip) ? $_SERVER['REMOTE_ADDR'] : $ip;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://httpapi.sinaapp.com/ip/?ip=' . $ip);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $ret = @json_decode(curl_exec($ch), 1);
    curl_close($ch);

    return $ret ? $ret['city'] : false;
} 

/**
 * 正则只允许中文数字字母下划线和横线
 * 依旧不废话。G了一下汉字的编码范围：
 * 
 * 1. GBK (GB2312/GB18030)
 * \x00-\xff GBK双字节编码范围
 * \x20-\x7f ASCII
 * \xa1-\xff 中文 gb2312
 * \x80-\xff 中文 gbk
 * 
 * 2. UTF-8 (Unicode)
 * 
 * \u4e00-\u9fa5 (中文)
 * \x3130-\x318F (韩文
 * \xAC00-\xD7A3 (韩文)
 * \u0800-\u4e00 (日文) 
 * filter_strings('到底dddadf_24-');
 */

function filter_strings($a)
{
    $r = preg_match("/^[a-zA-Z0-9\x7f-\xff_-]{3,16}$/", $a);
    var_dump($r);
} 

/**
 * * XML转换为数组
 * 注意$obj为SimpleXMLElement对象。调用：
 * 
 * $ret = convert_xml_to_array(simplexml_load_string($ret,'SimpleXMLElement',LIBXML_NOCDATA));
 * var_dump($ret);
 */
function convert_xml_to_array($obj)
{
    if (is_object($obj))
    {
        $obj = get_object_vars($obj);
    } 
    if (is_array($obj))
    {
        foreach ($obj as $key => $value)
        {
            $obj[$key] = convert_xml_to_array($value);
        } 
    } 
    return $obj;
} 

/**
 * SMTP邮件发送
 * $config = array();
 * $config['to'] = 'xxx@xx.com';
 * $config['subject'] = 'test';
 * $config['content'] = 'test content" target="_blank">test</a>';
 * $config['from'] = 'xx@xx.com';
 * 
 * $config['host'] = 'smtp.xx.com';
 * $config['user'] = 'xx@xx.com';
 * $config['pass'] = 'password';
 * 
 * var_dump(send_mail($config));
 */
function send_mail($config)
{
    $config = array_merge(array('charset' => 'UTF-8', 'port' => 25, 'ssl' => false), $config);
    $maildelimiter = "\r\n";
    $config['subject'] = '=?' . $config['charset'] . '?B?' . base64_encode(str_replace(array("/r", "/n"), null, $config['subject'])) . '?=';
    $config['content'] = chunk_split(base64_encode(str_replace("\r\n.", " \r\n..", str_replace("/n", "\r\n", str_replace("/r", "/n", str_replace("\r\n", "/n", str_replace("/n/r", "/r", $config['content']))))))); 
    // $email_from = preg_match('/^(.+?) /<(.+?)/>$/',$config['from'], $from) ? '=?'.$config['charset'].'?B?'.base64_encode($from['1'])."?= <".$from['2'].">" : $config['from'];
    $email_from = $config['from'];

    $headers = "From: $email_from{$maildelimiter}X-Priority: 3{$maildelimiter}X-Mailer: blog.csdn.net/yafeikf{$maildelimiter}MIME-Version: 1.0{$maildelimiter}Content-type: text/html;charset={$config['charset']}{$maildelimiter}Content-Transfer-Encoding: base64{$maildelimiter}"; 
    // connect mail server
    if (!$fp = @fsockopen(($config['ssl']?'ssl://':'') . $config['host'], $config['port'], $errno, $error, 5)) return 'connect error';
    stream_set_blocking($fp, 1); 
    // login mail server
    $lm = fread($fp, 512);
    if (substr($lm, 0, 3) != '220') return 'login' . $lm;
    fwrite($fp, "EHLO cevin\r\n");
    $lm = fread($fp, 512);
    if (!in_array(substr($lm, 0, 3), array('220', '250'))) return $lm;

    fwrite($fp, "AUTH LOGIN\r\n");
    $lm = fread($fp, 512);
    if (substr($lm, 0, 3) != '334') return $lm;

    fwrite($fp, base64_encode($config['user']) . "\r\n");
    $lm = fread($fp, 512);
    if (substr($lm, 0, 3) != '334') return $lm;

    fwrite($fp, base64_encode($config['pass']) . "\r\n");
    $lm = fread($fp, 512);
    if (substr($lm, 0, 3) != '235') return $lm; 
    // send mail header
    fwrite($fp, "MAIL FROM:<{$config['user']}>\r\n");
    $lm = fread($fp, 512);
    if (substr($lm, 0, 3) != '250') return $lm;

    fwrite($fp, "RCPT TO:<{$config['to']}>\r\n");
    $lm = fread($fp, 512);
    if (substr($lm, 0, 3) != '250') return $lm; 
    // send mail body with header
    fwrite($fp, "DATA\r\n");
    $lm = fread($fp, 512);
    if (substr($lm, 0, 3) != '354') return $lm;

    mt_srand();
    $headers .= 'Message-ID: <' . md5($config['content'] . microtime() . mt_rand(100000, 999999)) . ".>\r\n";

    fwrite($fp, 'Date: ' . gmdate('r') . "\r\n");
    fwrite($fp, "To: {$config['to']}\r\n");
    fwrite($fp, "Subject: {$config['subject']}\r\n");
    fwrite($fp, $headers . "\r\n\r\n\r\n");
    fwrite($fp, $config['content'] . "\r\n.\r\n");
    $lm = fread($fp, 512);
    if (substr($lm, 0, 3) != 250) return $lm; 
    // logout mail server
    fwrite($fp, "QUIT\r\n");
    return true;
} 

/**
 * 
 * @package 二维数组排序
 * @version $Id: FunctionsMain.inc.php,v 1.32 2011/09/24 11:38:37 wwccss Exp $


Sort an two-dimension array by some level two items use array_multisort() function.

sysSortArray($Array,"Key1","SORT_ASC","SORT_RETULAR","Key2";……)
 * @author lamp100 
 * @param array $ArrayData the array to sort.
 * @param string $KeyName1 the first item to sort by.
 * @param string $SortOrder1 the order to sort by("SORT_ASC"|"SORT_DESC")
 * @param string $SortType1 the sort type("SORT_REGULAR"|"SORT_NUMERIC"|"SORT_STRING")
 * @return array sorted array.
 * 
 * 
 * 有时候为了达到一定目的，需要对二维数组进行排序，现分享一下其实现的方法。
 * 有时候为了达到一定目的，需要对二维数组进行排序，现分享一下其实现的方法。
 * $arr=array (
 * '1' => array ( 'date' => '2011-08-18', 'num' => 5 ) ,
 * '2' => array ( 'date' => '2011-08-20', 'num' => 3 ) ,
 * '3' => array ( 'date' => '2011-08-17', 'num' => 10 )
 * )  ;
 * 这样运行之后的效果为：
 * $arr=array (
 * '1' => array ( 'date' => '2011-08-18', 'num' => 3 ) ,
 * '2' => array ( 'date' => '2011-08-20', 'num' => 5 ) ,
 * '3' => array ( 'date' => '2011-08-17', 'num' => 10 )
 * )  ;
 */
function sysSortArray($ArrayData, $KeyName1, $SortOrder1 = "SORT_ASC", $SortType1 = "SORT_REGULAR")
{
    if (!is_array($ArrayData))
    {
        return $ArrayData;
    } 
    // Get args number.
    $ArgCount = func_num_args(); 
    // Get keys to sort by and put them to SortRule array.
    for($I = 1;$I < $ArgCount;$I ++)
    {
        $Arg = func_get_arg($I);
        if (!eregi("SORT", $Arg))
        {
            $KeyNameList[] = $Arg;
            $SortRule[] = '$' . $Arg;
        } 
        else
        {
            $SortRule[] = $Arg;
        } 
    } 
    // Get the values according to the keys and put them to array.
    foreach($ArrayData AS $Key => $Info)
    {
        foreach($KeyNameList AS $KeyName)
        {
            ${$KeyName}[$Key] = $Info[$KeyName];
        } 
    } 
    // Create the eval string and eval it.
    $EvalString = 'array_multisort(' . join(",", $SortRule) . ',$ArrayData);';
    eval ($EvalString);
    return $ArrayData;
} 

/**
 * +----------------------------------------------------------
 * 字符串截取，支持中文和其他编码
 * +----------------------------------------------------------
 * 
 * @param string $source 需要转换的字符串
 * @param string $start 开始位置
 * @param string $length 截取长度
 * @param string $charset 编码格式
 * @param string $suffix 截断显示字符后缀
 * +----------------------------------------------------------
 * @return string +----------------------------------------------------------
 */
function xs_substr($source, $start = 0, $length, $charset = "utf-8", $suffix = "")
{
    if (function_exists("mb_substr")) // 采用PHP自带的mb_substr截取字符串
        {
            $string = mb_substr($source, $start, $length, $charset) . $suffix;
    } elseif (function_exists('iconv_substr')) // 采用PHP自带的iconv_substr截取字符串
        {
            $string = iconv_substr($source, $start, $length, $charset) . $suffix;
    } 
    else
    {
        $pattern['utf-8'] = "/[x01-x7f]|[xc2-xdf][x80-xbf]|[xe0-xef][x80-xbf]{2}|[xf0-xff][x80-xbf]{3}/";
        $pattern['gb2312'] = "/[x01-x7f]|[xb0-xf7][xa0-xfe]/";
        $pattern['gbk'] = "/[x01-x7f]|[x81-xfe][x40-xfe]/";
        $pattern['big5'] = "/[x01-x7f]|[x81-xfe]([x40-x7e]|xa1-xfe])/";
        preg_match_all($pattern[$charset], $source, $match);
        $slice = join("", array_slice($match[0], $start, $length));

        $string = $slice . $suffix;
    } 
    return $string;
} 

/**
 * 将数组或对象转换为XML文档也是我们作为开发经常要用到的，
 * 一般可能做接口提取数据时用的较多。
 * 下面两个函数组合可轻松实现数组或对象转XML：
 */
// xml编码
function xml_encode($data, $encoding = 'utf-8', $root = "root")
{
    $xml = '<?xml version="1.0" encoding="' . $encoding . '"?>';
    $xml .= '<' . $root . '>';
    $xml .= data_to_xml($data);
    $xml .= '</' . $root . '>';
    return $xml;
} 

function data_to_xml($data)
{
    if (is_object($data))
    {
        $data = get_object_vars($data);
    } 
    $xml = '';
    foreach ($data as $key => $val)
    {
        is_numeric($key) && $key = "item id=\"$key\"";
        $xml .= "<$key>";
        $xml .= (is_array($val) || is_object($val)) ? data_to_xml($val) : $val;
        list($key,) = explode(' ', $key);
        $xml .= "</$key>";
    } 
    return $xml;
} 

/**
 * 一般在评论系统中会经常用到展示IP地址，但为了不全部展示IP地址，所以要对IP地址做处理：隐藏最后一位或最后两位。
 * 以下是具体的实现方法： 
 * $ip = '255.255.255.255';
 * hide_ip_lastone( $ip );
 */
function hide_ip_lastone($ip)
{
    $reg1 = '/((?:\d+\.){3})\d+/';
    return preg_replace($reg1, "\\1*", $ip);
    /**
     * 以上输出结果为：255.255.255.*
     */
} 

function hide_ip_lasttwo($ip)
{
    $reg2 = '~(\d+)\.(\d+)\.(\d+)\.(\d+)~';
    return preg_replace($reg2, "$1.$2.*.*", $ip); //以上输出结果为：255.255.*.*
} 

/**
 * 函数名称:encrypt
 * 函数作用:加密解密字符串
 * 使用方法:
 * 加密     :encrypt('str','E','nowamagic');
 * 解密     :encrypt('被加密过的字符串','D','nowamagic');
 * 参数说明:
 * $string   :需要加密解密的字符串
 * $operation:判断是加密还是解密:E:加密   D:解密
 * $key      :加密的钥匙(密匙);
 * $id = 132;
 * $token = encrypt($id, 'E', 'nowamagic');
 * echo '加密:'.encrypt($id, 'E', 'nowamagic');
 * echo '<br />';
 * echo '解密：'.encrypt($token, 'D', 'nowamagic');
 */
function encrypt($string, $operation, $key = '')
{
    $key = md5($key);
    $key_length = strlen($key);
    $string = $operation == 'D'?base64_decode($string):substr(md5($string . $key), 0, 8) . $string;
    $string_length = strlen($string);
    $rndkey = $box = array();
    $result = '';
    for($i = 0;$i <= 255;$i++)
    {
        $rndkey[$i] = ord($key[$i % $key_length]);
        $box[$i] = $i;
    } 
    for($j = $i = 0;$i < 256;$i++)
    {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    } 
    for($a = $j = $i = 0;$i < $string_length;$i++)
    {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    } 
    if ($operation == 'D')
    {
        if (substr($result, 0, 8) == substr(md5(substr($result, 8) . $key), 0, 8))
        {
            return substr($result, 8);
        } 
        else
        {
            return'';
        } 
    } 
    else
    {
        return str_replace('=', '', base64_encode($result));
    } 
} 

/**
 * 写出五种方式来获取文件后缀名称,一个非常容易考试的题目
 * $filename  = 'www.baidu.com/images/logo.png';
 * 
 * //第一种使用strrchr函数进行字符串的截取
 * //先截取.后面的部分，然后再使用substr截取从1开始的字符串则可
 * echo "<br>" .  substr(strrchr($filename,'.'),1); 
 * 
 * //第二种方式使用pathinfo函数进行数组排列
 * $arr =  pathinfo($filename);
 * echo "<br>" . $arr['extension'];
 * 
 * //第三种方式使用strrpos函数,查找最后一个.的位置然后再使用substr截取该长度
 * echo "<br>" . substr($filename,(strrpos($filename,'.')+1));
 * 
 * //第四种巧妙的使用数组的方式进行获取 <img src="http://www.lnmp100.com/wp-includes/images/smilies/icon_smile.gif" alt=":-)" class="wp-smiley"> 
 * $ar = explode('.',$filename);
 * echo "<br>" . array_pop($ar);
 */
// 第五种则可使用正则表达式了
// echo "<br>" . (preg_replace('/.*\.(.*[^\.].*)*/iU','\\1',$filename));
function get_extname($filename)
{
    return substr(strrchr($filename, '.'), 1);
} 

function shorturl($input)
{
    $base62 = array ('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h',
        'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p',
        'q', 'r', 's', 't', 'u', 'v', 'w', 'x',
        'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G',
        'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P',
        'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y',
        'Z', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'
        );
    $hex = md5($input);
    $hexLen = strlen($hex);
    $subHexLen = $hexLen / 8;
    $output = array();
    for ($i = 0; $i < $subHexLen; $i++)
    {
        $subHex = substr ($hex, $i * 8, 8);

        $int = 0x3FFFFFFF &(1 * ('0x' . $subHex));
        $out = '';

        for ($j = 0; $j < 6; $j++)
        {
            $val = 0x0000003D &$int;

            $out .= $base62[$val];

            $int = $int >> 5;
        } 

        $output[] = $out;
    } 

    return $output;
} 　

?>