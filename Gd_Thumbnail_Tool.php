<?php
define('ROOT', dirname(__FILE__)); 
class Gd_Thumbnail_Tool
{
    protected static $image_w; //ͼ��Ŀ�
    protected static $image_h; //ͼ��ĸ�
    protected static $image_ext; //ͼ��ĺ�׺
     
    // ����ͼ����(��1Ҫ�����ͼ,��2�����Ŀ�,��3�����ĸ�)
    public static function mk_Thumb($image, $width = 160, $height = 160)
    { 
        // �����Ľ���ת�봦��
        $image = iconv('UTF-8', 'GB2312', $image); 
        // ��ȡͼƬ��Ϣ
        self :: image_Info($image); 
        // ��֤�Ƿ��ȡ����Ϣ
        if (empty(self :: $image_w) || empty(self :: $image_h) || empty(self :: $image_ext))
        {
            return false;
        } 
        // �ж�ͼƬ�Ĵ�С�Ƿ���Ҫ���еȱ�������
        if (self :: $image_w <= $width && self :: $image_h <= $height)
        {
            $yes = false;
        } 
        $yes = true; 
        // ����������
        if ($yes)
        {
            if ((self :: $image_w <= self :: $image_h) && (self :: $image_h > $height))
            { 
                // ���Ժ��
                $n = $height * (self :: $image_w / self :: $image_h);
                $small_w = round($n); 
                // ���Ժ��
                $small_h = $height;
            } 

            if ((self :: $image_w >= self :: $image_h) && (self :: $image_w > $width))
            { 
                // ���Ժ��
                $small_w = $width; 
                // ���Ժ��
                $n = $width * (self :: $image_h / self :: $image_w);
                $small_h = round($n);
            } 
        } 
        // ��ԭͼ������
        $a = 'imagecreatefrom' . self :: $image_ext;
        $original = $a($image); 
        // ����С����
        $litter = imagecreatetruecolor($width, $height); 
        // �Ѵ�ͼ���Է��뻭��
        $x = ($width - $small_w) / 2;
        $y = ($height - $small_h) / 2;
        if (!$rs = imagecopyresampled($litter, $original, $x, $y, 0, 0, $small_w, $small_h, self :: $image_w, self :: $image_h))
        {
            return false;
        } 
        // ����·��
        $path = self :: image_Dir() . self :: rand_Name() . '.' . self :: $image_ext; 
        // ����ͼƬ
        $keep = 'image' . self :: $image_ext;
        $keep($litter, $path); 
        // �ر�ͼƬ
        imagedestroy($original);
        imagedestroy($litter); 
        // ����·��
        return $path = strtr($path, array(ROOT => ''));
    } 
    // ��ȡͼƬ��Ϣ����
    protected static function image_Info($image)
    {
        if ($info = getimagesize($image))
        { 
            // ͼ��Ŀ�
            self :: $image_w = $info[0]; 
            // ͼ��ĸ�
            self :: $image_h = $info[1]; 
            // ͼ��ĺ�׺
            $ext = image_type_to_extension($info[2]);
            $ext = ltrim($ext, '.');
            self :: $image_ext = $ext;
        } 
    } 
    // ����·��
    protected static function image_Dir()
    {
        $dir = ROOT . 'Data/images/' . date('Y/m/d/', time());
        if (!is_dir($dir))
        {
            mkdir($dir, 0777, true);
        } 
        return $dir;
    } 
    // ����ļ���
    protected static function rand_Name()
    {
        $name = str_shuffle('1234567890qwertyuiopasdfghjklmnbvcxz');
        $name = substr($name, 0, 7);
        return $name = $name . '_smal';
    } 
} 
// ����
// echo Gd_Thumbnail_Tool::mk_Thumb('��.jpg');
// ����Ч��:Data/images/2013/02/01/ei3ufpr_smal.jpeg

?>