<?php
class CurlRequest
{
    private $ch = 0;
    /**
     * Init curl session
     * 
     * $params = array(‘url’ => ”,
     *                     ’host’ => ”,
     *                    ‘header’ => ”,
     *                    ‘method’ => ”,
     *                    ‘referer’ => ”,
     *                    ‘cookie’ => ”,
     *                    ‘post_fields’ => ”,
     *                     ['login' => '',]
     *                     ['password' => '',]
     *                    ‘timeout’ => 0
     *                    );
     */
    public function init($params)
    {
        if ($this -> ch == 0)
            $this -> ch = curl_init();
        $user_agent = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.8.0.9) Gecko/20061206 Firefox/1.5.0.9';
        $header = array("Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5",
            "Accept-Language: ru-ru,ru;q=0.7,en-us;q=0.5,en;q=0.3",
            "Accept-Charset: windows-1251,utf-8;q=0.7,*;q=0.7",
            "Keep-Alive: 300");
        if (isset($params['host']) && $params['host']) $header[] = "Host: " . $params['host'];
        if (isset($params['header']) && $params['header']) $header[] = $params['header'];

        @curl_setopt ($this -> ch , CURLOPT_RETURNTRANSFER , 1);
        @curl_setopt ($this -> ch , CURLOPT_VERBOSE , 1);
        @curl_setopt ($this -> ch , CURLOPT_HEADER , 1);
        if (strlen($params['proxy']) > 0) curl_setopt($this -> ch, CURLOPT_PROXY, $params['proxy']);
        if (strlen($params['proxyport']) > 0) curl_setopt($this -> ch, CURLOPT_PROXYPORT, $params['proxyprot']);
        curl_setopt($this -> ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        if ($params['method'] == "HEAD") @curl_setopt($this -> ch, CURLOPT_NOBODY, 1);
        @curl_setopt ($this -> ch, CURLOPT_FOLLOWLOCATION, 1);
        @curl_setopt ($this -> ch , CURLOPT_HTTPHEADER, $header);
        if ($params['referer']) @curl_setopt ($this -> ch , CURLOPT_REFERER, $params['referer']);
        @curl_setopt ($this -> ch , CURLOPT_USERAGENT, $user_agent);
        if ($params['cookie']) @curl_setopt ($this -> ch , CURLOPT_COOKIE, $params['cookie']);

        if ($params['method'] == "POST")
        {
            curl_setopt($this -> ch, CURLOPT_POST, true);
            curl_setopt($this -> ch, CURLOPT_POSTFIELDS, $params['post_fields']);
        } 
        @curl_setopt($this -> ch, CURLOPT_URL, $params['url']);
        @curl_setopt ($this -> ch , CURLOPT_SSL_VERIFYPEER, 0);
        @curl_setopt ($this -> ch , CURLOPT_SSL_VERIFYHOST, 0);
        if (isset($params['login']) &isset($params['password']))
            @curl_setopt($this -> ch , CURLOPT_USERPWD, $params['login'] . ':' . $params['password']);
        @curl_setopt ($this -> ch , CURLOPT_TIMEOUT, $params['timeout']);
    } 

    /**
     * Make curl request
     * 
     * @return array ’header’,'body’,'curl_error’,'http_code’,'last_url’
     */
    public function exec()
    {
        $response = curl_exec($this -> ch);
        $error = curl_error($this -> ch);
        $result = array('header' => '',
            'body' => '',
            'curl_error' => '',
            'http_code' => '',
            'last_url' => '');
        if ($error != "")
        {
            $result['curl_error'] = $error;
            return $result;
        } 

        $header_size = curl_getinfo($this -> ch, CURLINFO_HEADER_SIZE);
        $result['header'] = $this -> pass_header(substr($response, 0, $header_size));
        $result['body'] = substr($response, $header_size);
        $result['http_code'] = curl_getinfo($this -> ch, CURLINFO_HTTP_CODE);
        $result['last_url'] = curl_getinfo($this -> ch, CURLINFO_EFFECTIVE_URL);
        $result[‘last_sent’] = curl_getinfo($this -> ch, CURLINFO_HEADER_OUT);
        return $result;
    } 
    function __destruct()
    {
        @curl_close($this -> ch);
    } 
    public function pass_header($header)
    {
        $result = array();
        $varHader = explode("\r\n", $header);
        if (count($varHader) > 0)
        {
            for($i = 0;$i < count($varHader);$i++)
            {
                $varresult = explode(":", $varHader[$i]);
                if (is_array($varresult) && isset($varresult[1]))
                    $result[$varresult[0]] = $varresult[1];
            } 
        } 
        return $result;
    } 
} 

?>

<?php
//include ‘curl.inc.php’;
$GLOBALS['curl']=new CurlRequest;
function verify($proxy,$prot)
{
$key=rand('1000000','9999999');
$params = array('url' => "http://xxxxx.com/a.php?md=$key",   //自己找个服务器放个程序！
                       'host' => 'help.dhgate.com',
                       'proxy' => "$proxy:$prot",
                       'proxyport'=>"$prot",
                       'timeout' =>10
                       );
$GLOBALS['curl']->init($params);
$result=$GLOBALS['curl']->exec();
if(trim($result['body'])==md5($key.'my'))
        return true;
else
        return false;
}
echo verify('localhost','7777');
?>
目标服务器的那个程序。。。
<?php
echo md5($_GET['md'].'my');
?>
设计初衷当然是用php curl bind不一个sokcet5去访问一个随机页面（公网）这个页面的功能就是通过一个md5来验证sokcet5服务器是否真的可用
A服务器通过 socket5发出http://xxxx/a.php?t=randkey
B服务器，echo md5($_GET['t'] . mykey);
A服务器拿到content以后也用相同算法算一个$resut看看是不是等于B服务器的echo值，如果一样，就是success 不一样就是failed
verify 函数返回的true false标志socket5服务器是否可用！
curl_setopt($this->ch,CURLOPT_PROXYTYPE,CURLPROXY_SOCKS5); 这行话，是专门给socket5用的你要验证http proxy的也一样，删了这行就行了！
