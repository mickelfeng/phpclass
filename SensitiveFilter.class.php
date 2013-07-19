<?php
/**
 * 敏感词汇过滤
 * User: konakona
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
