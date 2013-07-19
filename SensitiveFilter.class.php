<?php
/**
 * 敏感词汇过滤
 * Date: 12-11-28
 * Time: 下午4:37
 * 调用方式
 * if(false === SensitiveFilter::filter($content)){
 *      error("含有敏感词汇");
 * }
 */
class SensitiveFilter extends Think{
    public static $wordArr = array();
    public static $content = "";
    /**
     * 处理内容
     * @param $content
     *
     * @return bool
     */
    public static function filter($content){
        if($content=="") return false;
        self::$content = $content;
        empty(self::$wordArr)?self::getWord():"";
        foreach ( self::$wordArr as $row){
            if (false !== strstr(self::$content,$row)) return false;
        }
        return true;
    }
    public static function getWord(){
        self::$wordArr = include 'SensitiveThesaurus.php';
    }
} 
/*
如果内容中包含敏感词汇，则返回False，否则返回True。

很简单的代码。

请将文件放置于 "项目/ORG/SensitiveFilter.class.php"下。

其中 “ SensitiveThesaurus.php”是一个敏感词汇数组，大家可以任意添加内容。
*/
