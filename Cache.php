<?php
//载入类
require_once("Cache.class.php");
//cache数据保存根目录
$sRootDir = "d:/apply/Apache/htdocs/demo2";
//初始化对象
$oCache = new Cache($sRootDir);
//应用名称
$sAppName     = "personal";
//需cache的区块变量
$sVariable = "xxxx";
//需cache的原始数据，支持数组、字串
$sDataRec     = array('name'=>'xxx', 'sex'=>1);
//获得cache数据
$aRet = $oCache->getCache($sAppName, $sVariable);
print_r($aRet);
//检查cache是否有效
$bCacheCheck = $oCache->cacheIsValid($aRet[0]);
if(count($aRet) <= 0 || !$bCacheCheck)
{
     echo "xxx";
    //写入、更新cache数据
     $bRet = $oCache->setCache($sAppName, $sVariable, $sDataRec);
     print_r($bRet);
}
?>

Cache.class.php 源文件

<?php
/*
    * class of cache, cache variable、dataArray、pageContent
    * author:zhirui
    * version:1.0
    * update:2007-04-13
    */
class Cache {
     var $sCacheRoot;          //cache根目录
     var $iCacheVaildTime;     //cache有效期(时间戳)，默认为1小时(60*60=3600秒)
     /*
         * construct
         *
         * @param     string     $sCacheRootDir cache     存放根目录
         * @param     integer     $iCacheVaildTime          cache有效时间(精确到秒)
         */
     function Cache($sCacheRootDir = ".", $iCacheVaildTime = 3600)
     {
          $this->sCacheRoot = $sCacheRootDir;
          $this->iCacheVaildTime = $iCacheVaildTime > 0 ? $iCacheVaildTime : 3600;
     }
     /*
         * read cache data
         *
         * @param     string     $sAppName          应用名称
         * @param     string     $sVariableName     变量名称
         * @return     array     $aDataRec          返回结果数组
         * @access     public
         */
     function getCache($sAppName, $sVariableName)
     {
          $aDataRec = array();
          if(empty($sAppName) || empty($sVariableName))
          {
               return $aDataRec;
          }
          //计算cache数据路径、文件信息
          $aCacheFile = $this->_calculateFile($sAppName, $sVariableName);
          if(!is_array($aCacheFile) || count($aCacheFile) < 2)
          {
               return $aDataRec;
          }
          //重组cache文件信息、读取cache数据
          $sCacheFile = $this->sCacheRoot."/".$aCacheFile["dir"]."/".$aCacheFile["file"];
          return $this->_readFile($sCacheFile);
     }
    /*
         * write cache data
         *
         * @param     string     $sAppName                    应用名称
         * @param     string     $sVariableName               变量名称
         * @param     array     $aDataRec|$sDataRec          变量对应记录数组或字串
         * @return     boolean     $bReturn                    成功:true 失败:false
         * @access     public
         */
     function setCache($sAppName, $sVariableName, $aDataRec)
     {
          $bReturn = false;
          if(empty($sAppName) || empty($sVariableName) || (empty($aDataRec) && !is_array($aDataRec)))
          {
               return $bReturn;
          }
         //计算cache数据路径、文件信息
          $aCacheFile = $this->_calculateFile($sAppName, $sVariableName);
          if(!is_array($aCacheFile) || count($aCacheFile) < 2)
          {
               return $bReturn;
          }
          //重组cache文件信息、读取cache数据
          $sCacheFile = $this->sCacheRoot."/".$aCacheFile["dir"]."/".$aCacheFile["file"];
          //写入数据
          $iIsArray = is_array($aDataRec) ? 1 : 0;
          $bReturn = $this->_writeFile($sCacheFile, $aDataRec, $iIsArray);
          return $bReturn;
     }
    /*
         * check cache is valid
         *
         * @param     integer          $iCacheLastTime          cache上次更新时间(timestamp)
         * @param     integer          $iCacheLimit          cache保持时长 默认3600秒
         * @return     boolean          $bReturn               返回值     有效:true     无效:false
         * @access     public
         */
     function cacheIsValid($iCacheLastTime, $iCacheLimit = 3600)
     {
          if(!is_numeric($iCacheLastTime) || $iCacheLastTime <= 0)
          {
               return $false;
          }
          $iTimeLimit = (time() - $iCacheLastTime);
          if($iTimeLimit > $iCacheLimit)
          {
               return false;
          }
          return true;
     }
     /*
         * calculate file path and name
         *
         * @param     string     $sAppName          应用名称
         * @param     string     $sVariableName     变量名称
         * @return     array     $aDataRec          结果数组，包括path、文件名
         * @access     private
         */
     function _calculateFile($sAppName, $sVariableName)
     {
          $aDataRec = array();
          if(empty($sAppName) || empty($sVariableName))
          {
               return $aDataRec;
          }
          //app dir
          $sAppDir     = $this->sCacheRoot."/".$sAppName;
          $sRetPath = $sAppName; //返回的cache所在目录
          if(!is_dir($sAppDir))
          {
               $bRet = mkdir($sAppDir, 0777);
               if(!$bRet)
               {
                    return $aDataRec;
               }
          }
          //hash dir
          $sTmpStr     = md5($sVariableName);
          $sSigChar = substr($sTmpStr, 0, 1);
          $sHashDir = ord($sSigChar);
          $sCacheDirTwo = $sAppDir."/".$sHashDir;
          if(!is_dir($sCacheDirTwo))
          {
               $bRet = mkdir($sCacheDirTwo, 0777);
               if(!$bRet)
               {
                    return $aDataRec;
               }
          }
          $sRetPath .= "/".$sHashDir;
          $sCacheDirThree = $sCacheDirTwo."/".$sSigChar;
          if(!is_dir($sCacheDirThree))
          {
               $bRet = mkdir($sCacheDirThree, 0777);
               if(!$bRet)
               {
                    return $aDataRec;
               }
          }
          $sRetPath .= "/".$sSigChar;
         //最终返回的cache所在目录
          $aDataRec["dir"] = $sRetPath;
          //计算cache文件名
          $sTmpFileStr =     $sRetPath."/".$sVariableName;
          $sCacheFile     = md5($sTmpFileStr).".php";
          //最终返回的cache文件名
          $aDataRec["file"] = $sCacheFile;
         return $aDataRec;
     }
      /*
         * read cache file content
         *
         * @param     string     $sFileName     待读取文件名
         * @return     array     $aDataRec     记录数组
         * @access     private
         */
     function _readFile($sFileName)
     {
          $aDataRec = array();
          if(!file_exists($sFileName))
          {
               return $aDataRec;
          }
         //动态载入cache数据
          require($sFileName);
          $aDataRec = isset($dataArr) ? $dataArr : NULL;
          return $aDataRec;
     }
     /*
         * write cache file content
         *
         * @param     string     $sFileName 待写入文件名
         * @param     array     $aDataRec     记录数组
         * @param     integer     $iIsArray     是否为数组数据 1:是 0:否 默认0
         * @return boolean     $bReturn     成功:true 失败:false;
         * @access private
         */
     function _writeFile($sFileName, $aDataRec, $iIsArray = 0)
     {
          $bCheck = false;
          $bCheck = $iIsArray ? (is_array($aDataRec) ? true : false) : (!empty($aDataRec) ? true : false);
          if(empty($sFileName) || !$bCheck)
          {
               return false;
          }
          $fp = fopen($sFileName, "w");
          if(!$fp)
          {
               return false;
          }
          flock($fp, LOCK_EX);
         //数据写入时间, 为有效期提供依据, UNIX 时间戳
          $sUpTime = time();
          //数据内容处理
          $aDataContent = array();
          $aDataContent[0] = $sUpTime;
          $aDataContent[1] = $iIsArray ? $aDataRec : $aDataRec;
          //数据格式化, 转换为php类型数据
          $sPhpData = "<?php\n";
              $sPhpData .= "$dataArr = ".var_export($aDataContent, true).";\n";
              $sPhpData .= "?>";
          fwrite($fp, $sPhpData);
          flock($fp, LOCK_UN);
          fclose($fp);
          return true;
     }
}
?> 
