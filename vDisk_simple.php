<?php
require_once('vDisk.class.php');
require_once('../class/e_vDisk.class.php');
/**
 * 重新封装的微盘类
 * 
 * @author aimsam
这里的链接是是微盘PHP的SDK:http://vdisk.me/api/doc 
 */
class vDisk_simple
{
    private $appkey; //
    private $appsecret;
    private $username;
    private $password;
    private $token;
    private $vDisk;

    /**
     * 构造方法
     * 
     * @param unknown_type $app_key 
     * @param unknown_type $app_secret 
     * @param unknown_type $username 
     * @param unknown_type $password 
     */
    public function __construct($vDisk)
    {
        if (!($vDisk -> getAppkey() && $vDisk -> getAppsecret() && $vDisk -> getUsername() && $vDisk -> getPassword()))
        {
            $this -> set_error(-2, 'app_key or app_secret or password or username empty');
            return;
        } 

        $this -> vDisk = new vDisk($vDisk -> getAppkey(), $vDisk -> getAppsecret()); //创建微盘
        $this -> vDisk -> get_token($vDisk -> getUsername(), $vDisk -> getPassword()); //获取token
        $_SESSION['token'] = $vdisk -> token;
        $back = $this -> vDisk -> keep_token(); //保持token
        return $back['err_msg'];
    } 

    /**
     * 上传文件（10M以下）
     * 
     * @param unknown_type $file 
     * @param unknown_type $cover 
     */
    public function upload_file($file, $cover = 'yes')
    {
        if ($file["error"] > 0)
        {
            $data['msg'] == "file_error"; //文件错误
        } 
        else if ($file["size"] / 1024 / 1024 > 10)
        {
            $data['msg'] == "size_error"; //文件大于10MB
        } 
        else
        {
            $back = $this -> vDisk -> upload_file($file["tmp_name"], 0);
            $data['fid'] = $back['data']['fid'];
            $random = date(“Ymd”) . "_" . date("His") . "_" . rand(1000, 9999);

            $this -> vDisk -> rename_file($back['data']['fid'], $random . "_" . $file["name"]);
            $back = $this -> vDisk -> get_file_info($data['fid']);
            $data['msg'] = $back['err_msg'];
        } 
        return $data;
    } 

    /**
     * 获得下载地址
     * 
     * @param unknown_type $fid 
     */
    public function get_download_url($fid)
    {
        $back = $this -> vDisk -> get_file_info($fid);

        if ($back['err_msg'] == 'success')
        {
            $data['msg'] = "success";
            $data['fid'] = $back['data']['id'];
            $data['url'] = $back['data']['s3_url'];
        } 
        else
        {
            $data['msg'] = "error";
        } 

        return $data;
    } 

    /**
     * 获得剩余容量
     */
    public function get_rest_area()
    {
        $back = $this -> vDisk -> get_quota();
        $data["msg"] = $back["err_msg"];
        $data["used"] = $back["data"]["used"];
        $data["total"] = $back["data"]["total"];
        $data["rest"] = $back["data"]["total"]–$back["data"]["used"];

        return $data;
    } 

    /**
     * 删除文件
     * 
     * @param unknown_type $fid 
     */
    public function delete_file($fid)
    {
        return $this -> vDisk -> delete_file($fid);
    } 

    /**
     * 获得文件列表
     */
    public function get_list()
    {
        $back = $this -> vDisk -> get_list(0);
        foreach($back["data"] as $temp)
        {
            $temp2["fid"] = $temp["id"];
            $temp2["name"] = $temp["name"];
            $data[] = $temp2;
        } 

        return $data;
    } 

    /**
     * 是否可用
     */
    public function isUsed()
    {
        $back = $this -> vDisk -> keep_token(); //保持token
        if (!$back['err_msg'] != 'success')
        {
            return false; //密码错误
        } 
        $temp = get_rest_area();
        if (($temp['rest'] / 1024 / 1024) < 50) // 如果小于50M
            {
                return false;
        } 

        return true;
    } 
} 

?>
