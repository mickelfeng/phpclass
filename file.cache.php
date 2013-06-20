<?php
class cache
{ 
    // 缓存文件路径，根据实际路径修改，要以/结束
    private $path = "./";

    function __construct($path = './')
    {
        $this -> path = $path;
    } 
    /**
     * 取缓存数据
     * $key:缓存的key，会进行md5处理，所以送任何值都可以，包括sql语句
     * $time:缓存有效期，秒，0=永不失效
     */
    public function getCache($key, $time = 0)
    {
        $file = $this -> getdir($key);
        if (file_exists($file))
        {
            if ($time == 0 || filemtime($file) + $time > time())
            {
                return file_get_contents($file);
            } 
            else
            { 
                // 失效文件删除
                unlink($file);
                return false;
            } 
        } 
        else
        {
            return false;
        } 
    } 

    /**
     * 存缓存
     */
    public function setCache($key, $contents)
    {
        $file = $this -> getdir($key, true);
        file_put_contents($file, $contents);
    } 

    /**
     * 根据key获取缓存文件路径，根据最后两位md5，分成两级子目录
     */
    private function getdir($key, $mkdir = false)
    {
        $newkey = md5($key);
        if ($mkdir && !is_dir($this -> path . substr($newkey, 30, 1)))
            mkdir($this -> path . substr($newkey, 30, 1));
        if ($mkdir && !is_dir($this -> path . substr($newkey, 30, 1) . '/' . substr($newkey, 31, 1)))
            mkdir($this -> path . substr($newkey, 30, 1) . '/' . substr($newkey, 31, 1));
        $path = $this -> path . substr($newkey, 30, 1) . '/' . substr($newkey, 31, 1) . '/' . $newkey;
        return $path;
    } 
} 
// 调用代码
$mycache = new cache("./cache/");
// 存缓存
$mycache -> setCache('test', 'hello');
// 读缓存
$test = $mycache -> getCache('test', 1);
if ($test !== false)
    echo $test;
else
    echo '失效或没有记录';

?>
