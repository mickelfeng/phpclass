<?php
define('ROOT', dirname(__FILE__)); 
class Gd_Thumbnail_Tool
{
    protected static $image_w; //图像的宽
    protected static $image_h; //图像的高
    protected static $image_ext; //图像的后缀
     
    // 缩略图方法(参1要处理的图,参2处理后的宽,参3处理后的高)
    public static function mk_Thumb($image, $width = 160, $height = 160)
    { 
        // 对中文进行转码处理
        $image = iconv('UTF-8', 'GB2312', $image); 
        // 获取图片信息
        self :: image_Info($image); 
        // 验证是否获取到信息
        if (empty(self :: $image_w) || empty(self :: $image_h) || empty(self :: $image_ext))
        {
            return false;
        } 
        // 判断图片的大小是否需要进行等比例缩略
        if (self :: $image_w <= $width && self :: $image_h <= $height)
        {
            $yes = false;
        } 
        $yes = true; 
        // 按比例缩略
        if ($yes)
        {
            if ((self :: $image_w <= self :: $image_h) && (self :: $image_h > $height))
            { 
                // 缩略后宽
                $n = $height * (self :: $image_w / self :: $image_h);
                $small_w = round($n); 
                // 缩略后高
                $small_h = $height;
            } 

            if ((self :: $image_w >= self :: $image_h) && (self :: $image_w > $width))
            { 
                // 缩略后宽
                $small_w = $width; 
                // 缩略后高
                $n = $width * (self :: $image_h / self :: $image_w);
                $small_h = round($n);
            } 
        } 
        // 以原图做画布
        $a = 'imagecreatefrom' . self :: $image_ext;
        $original = $a($image); 
        // 创建小画布
        $litter = imagecreatetruecolor($width, $height); 
        // 把大图缩略放入画布
        $x = ($width - $small_w) / 2;
        $y = ($height - $small_h) / 2;
        if (!$rs = imagecopyresampled($litter, $original, $x, $y, 0, 0, $small_w, $small_h, self :: $image_w, self :: $image_h))
        {
            return false;
        } 
        // 保存路径
        $path = self :: image_Dir() . self :: rand_Name() . '.' . self :: $image_ext; 
        // 保存图片
        $keep = 'image' . self :: $image_ext;
        $keep($litter, $path); 
        // 关闭图片
        imagedestroy($original);
        imagedestroy($litter); 
        // 返回路径
        return $path = strtr($path, array(ROOT => ''));
    } 
    // 获取图片信息方法
    protected static function image_Info($image)
    {
        if ($info = getimagesize($image))
        { 
            // 图像的宽
            self :: $image_w = $info[0]; 
            // 图像的高
            self :: $image_h = $info[1]; 
            // 图像的后缀
            $ext = image_type_to_extension($info[2]);
            $ext = ltrim($ext, '.');
            self :: $image_ext = $ext;
        } 
    } 
    // 生成路径
    protected static function image_Dir()
    {
        $dir = ROOT . 'Data/images/' . date('Y/m/d/', time());
        if (!is_dir($dir))
        {
            mkdir($dir, 0777, true);
        } 
        return $dir;
    } 
    // 随机文件名
    protected static function rand_Name()
    {
        $name = str_shuffle('1234567890qwertyuiopasdfghjklmnbvcxz');
        $name = substr($name, 0, 7);
        return $name = $name . '_smal';
    } 
} 
// 测试
// echo Gd_Thumbnail_Tool::mk_Thumb('啊.jpg');
// 返回效果:Data/images/2013/02/01/ei3ufpr_smal.jpeg

?>