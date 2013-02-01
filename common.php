<?php
/**
 * ��һ��URLת��Ϊ����URL
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



// ���·��ת���ɾ���·��
function relative_to_absolute($content, $feed_url)
{
    preg_match('/(http|https|ftp):///', $feed_url, $protocol);
    $server_url = preg_replace("/(http|https|ftp|news):///", "", $feed_url); 
    // ��ԴOSPhP.COM.CN
    $server_url = preg_replace("//.*/", "", $server_url);
    if ($server_url == '')
    {
        return $content;
    } 
    if (isset($protocol[0]))
    { 
        // ��Դ����OSPhP.COm.CN
        $new_content = preg_replace('/href="//', 'href="' . $protocol[0] . $server_url . '/', $content);
        $new_content = preg_replace('/src="//', 'src="' . $protocol[0] . $server_url . '/', $new_content); //��ԴOSPhP.COM.CN 
    } 
    else
    {
        $new_content = $content;
    } 
    return $new_content;
} 
/**
 * ֱ�Ӽ�¼��־
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
 * * �ж����������
 */
function get_user_agent_lang()
{
    $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 4); //ֻȡǰ4λ������ֻ�ж������ȵ����ԡ����ȡǰ5λ�����ܳ���en,zh�������Ӱ���жϡ�  
    if (preg_match("/zh-c/i", $lang))
        return "��������";
    else if (preg_match("/zh/i", $lang))
        return "���w����";
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
 * ����ĺ����������������û������룬��֤������XSS��ȫ�ġ�������ι��ˣ����Բο������ڲ���Ҳ��ע�͡�
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
 * ��¼һ����־�������������ַ�ʽ���γ���д��־��
 * - ��ǰ����ָ�����ļ�д����־��
 * - ������php.ini��ָ����error_logд���ݡ�
 * - ��ϵͳ��־д���ݣ�����ʧ�ܵĻ��򷵻�false��
 * 
 * ����ÿ�ε���ʱ��ָ��logFile��dateFormat����
 * ϵͳ���Զ���ס�ϴ�ָ�������ݡ�
 * 
 * PHP5.0֮����ȷ���Ѿ����ú�ʱ����������ܻ��׳�һ������
 * example:
 * 
 * @code php
 * // ��һ�ε��ã���ʼ����־����д���һ����Ϣ��
 * logg('init...', LOG_INFO, '/usr/log.txt', 'y-m-d');
 * // д��־
 * logg('log msg', LOG_INFO);
 * @endcode 
 * @param string $message ��־����
 * @param int $type ��־���ͣ�����syslog�����Ĳ���
 * @param string $logFile ��־�ļ�
 * @param string $dateFormat ��־��ʱ���ʽ
 * @return bool �Ƿ�ɹ�д��
 * @staticvar array $types ����$type��Ӧ��������Ϣ��
 * @staticvar string $file ����$logFile�������δ��ݵ����ݡ�
 * @staticvar string $format ����$dateFormat������󴫵ݵ����ݡ�
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
        // windows�£�����������ֵ��һ����
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
     * ��ʽ����Ϣ
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
 * ��ȡ�û���ʵ IP
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
 * ��ȡ IP  ����λ��
 * �Ա�IP�ӿ�
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
 * ��ɫ��ǩTags
 * �ڿ����������ȡ��ǩ�б�ķ���
 * //��ǩ��������ѯ��ǩ���Ի�ȡ��ǩ�б�   
 * function Tags($Module)
 * {
 *                 $Tag = M('Tag');
 *                 $map['module'] = $Module;
 *                 $Tagslist = $Tag -> where($map) -> field('id,name,count') -> order('count desc') -> limit('0,25') -> select();
 *                 $this -> assign('tags', $Tagslist);
 *                 $this -> display();
 * } 
 * �ѱ�ǩ�б������ģ���� 
 * <volist id="vo" name="tags" >   
 * <li><a href="{:U('/web','tag='.$vo['name']|urlencode)}">
 * <span style="font-size:{color:{$vo.id|rand_color}">&nbsp;&nbsp;{$vo.name}[{$vo.count}]</span>
 * </a></li>   
 * </volist>
 */

function rcolor()
{
    $rand = rand(0, 255); //�����ȡ0--255������   
    return sprintf("%02X", "$rand"); //���ʮ�����Ƶ�������д��ĸ   
} 

function rand_color()
{
    return '#' . rcolor() . rcolor() . rcolor(); //������ĸ   
} 

/**
 * ����IP��ַ��ȡ������Ϣ
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
 * ����ֻ��������������ĸ�»��ߺͺ���
 * ���ɲ��ϻ���G��һ�º��ֵı��뷶Χ��
 * 
 * 1. GBK (GB2312/GB18030)
 * \x00-\xff GBK˫�ֽڱ��뷶Χ
 * \x20-\x7f ASCII
 * \xa1-\xff ���� gb2312
 * \x80-\xff ���� gbk
 * 
 * 2. UTF-8 (Unicode)
 * 
 * \u4e00-\u9fa5 (����)
 * \x3130-\x318F (����
 * \xAC00-\xD7A3 (����)
 * \u0800-\u4e00 (����) 
 * filter_strings('����dddadf_24-');
 */

function filter_strings($a)
{
    $r = preg_match("/^[a-zA-Z0-9\x7f-\xff_-]{3,16}$/", $a);
    var_dump($r);
} 

/**
 * * XMLת��Ϊ����
 * ע��$objΪSimpleXMLElement���󡣵��ã�
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
 * SMTP�ʼ�����
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
 * @package ��ά��������
 * @version $Id: FunctionsMain.inc.php,v 1.32 2011/09/24 11:38:37 wwccss Exp $


Sort an two-dimension array by some level two items use array_multisort() function.

sysSortArray($Array,"Key1","SORT_ASC","SORT_RETULAR","Key2";����)
 * @author lamp100 
 * @param array $ArrayData the array to sort.
 * @param string $KeyName1 the first item to sort by.
 * @param string $SortOrder1 the order to sort by("SORT_ASC"|"SORT_DESC")
 * @param string $SortType1 the sort type("SORT_REGULAR"|"SORT_NUMERIC"|"SORT_STRING")
 * @return array sorted array.
 * 
 * 
 * ��ʱ��Ϊ�˴ﵽһ��Ŀ�ģ���Ҫ�Զ�ά������������ַ���һ����ʵ�ֵķ�����
 * ��ʱ��Ϊ�˴ﵽһ��Ŀ�ģ���Ҫ�Զ�ά������������ַ���һ����ʵ�ֵķ�����
 * $arr=array (
 * '1' => array ( 'date' => '2011-08-18', 'num' => 5 ) ,
 * '2' => array ( 'date' => '2011-08-20', 'num' => 3 ) ,
 * '3' => array ( 'date' => '2011-08-17', 'num' => 10 )
 * )  ;
 * ��������֮���Ч��Ϊ��
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
 * �ַ�����ȡ��֧�����ĺ���������
 * +----------------------------------------------------------
 * 
 * @param string $source ��Ҫת�����ַ���
 * @param string $start ��ʼλ��
 * @param string $length ��ȡ����
 * @param string $charset �����ʽ
 * @param string $suffix �ض���ʾ�ַ���׺
 * +----------------------------------------------------------
 * @return string +----------------------------------------------------------
 */
function xs_substr($source, $start = 0, $length, $charset = "utf-8", $suffix = "")
{
    if (function_exists("mb_substr")) // ����PHP�Դ���mb_substr��ȡ�ַ���
        {
            $string = mb_substr($source, $start, $length, $charset) . $suffix;
    } elseif (function_exists('iconv_substr')) // ����PHP�Դ���iconv_substr��ȡ�ַ���
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
 * ����������ת��ΪXML�ĵ�Ҳ��������Ϊ��������Ҫ�õ��ģ�
 * һ��������ӿ���ȡ����ʱ�õĽ϶ࡣ
 * ��������������Ͽ�����ʵ����������תXML��
 */
// xml����
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
 * һ��������ϵͳ�лᾭ���õ�չʾIP��ַ����Ϊ�˲�ȫ��չʾIP��ַ������Ҫ��IP��ַ�������������һλ�������λ��
 * �����Ǿ����ʵ�ַ����� 
 * $ip = '255.255.255.255';
 * hide_ip_lastone( $ip );
 */
function hide_ip_lastone($ip)
{
    $reg1 = '/((?:\d+\.){3})\d+/';
    return preg_replace($reg1, "\\1*", $ip);
    /**
     * ����������Ϊ��255.255.255.*
     */
} 

function hide_ip_lasttwo($ip)
{
    $reg2 = '~(\d+)\.(\d+)\.(\d+)\.(\d+)~';
    return preg_replace($reg2, "$1.$2.*.*", $ip); //����������Ϊ��255.255.*.*
} 

/**
 * ��������:encrypt
 * ��������:���ܽ����ַ���
 * ʹ�÷���:
 * ����     :encrypt('str','E','nowamagic');
 * ����     :encrypt('�����ܹ����ַ���','D','nowamagic');
 * ����˵��:
 * $string   :��Ҫ���ܽ��ܵ��ַ���
 * $operation:�ж��Ǽ��ܻ��ǽ���:E:����   D:����
 * $key      :���ܵ�Կ��(�ܳ�);
 * $id = 132;
 * $token = encrypt($id, 'E', 'nowamagic');
 * echo '����:'.encrypt($id, 'E', 'nowamagic');
 * echo '<br />';
 * echo '���ܣ�'.encrypt($token, 'D', 'nowamagic');
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
 * д�����ַ�ʽ����ȡ�ļ���׺����,һ���ǳ����׿��Ե���Ŀ
 * $filename  = 'www.baidu.com/images/logo.png';
 * 
 * //��һ��ʹ��strrchr���������ַ����Ľ�ȡ
 * //�Ƚ�ȡ.����Ĳ��֣�Ȼ����ʹ��substr��ȡ��1��ʼ���ַ������
 * echo "<br>" .  substr(strrchr($filename,'.'),1); 
 * 
 * //�ڶ��ַ�ʽʹ��pathinfo����������������
 * $arr =  pathinfo($filename);
 * echo "<br>" . $arr['extension'];
 * 
 * //�����ַ�ʽʹ��strrpos����,�������һ��.��λ��Ȼ����ʹ��substr��ȡ�ó���
 * echo "<br>" . substr($filename,(strrpos($filename,'.')+1));
 * 
 * //�����������ʹ������ķ�ʽ���л�ȡ <img src="http://www.lnmp100.com/wp-includes/images/smilies/icon_smile.gif" alt=":-)" class="wp-smiley"> 
 * $ar = explode('.',$filename);
 * echo "<br>" . array_pop($ar);
 */
// ���������ʹ��������ʽ��
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
} ��

?>