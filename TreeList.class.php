<?php
/**
 * 输出无限分类,我自己写的哦~
 * 
 * @author binny_w@qq.com
 * @since 2013-09-24 AM
 */
/* 使用示例 */
/*
$arrAll = array(
    array('id' => 1, 'name' => '栏目分类_1', 'name_en' => 'cat_1', 'parent_id' => 0),
    array('id' => 2, 'name' => '栏目分类_2', 'name_en' => 'cat_2', 'parent_id' => 0),
    array('id' => 3, 'name' => '栏目分类_3', 'name_en' => 'cat_3', 'parent_id' => 1),
    array('id' => 4, 'name' => '栏目分类_4', 'name_en' => 'cat_4', 'parent_id' => 1),
    array('id' => 5, 'name' => '栏目分类_5', 'name_en' => 'cat_5', 'parent_id' => 2),
    array('id' => 6, 'name' => '栏目分类_6', 'name_en' => 'cat_6', 'parent_id' => 4),
    array('id' => 7, 'name' => '栏目分类_7', 'name_en' => 'cat_7', 'parent_id' => 6),
    array('id' => 8, 'name' => '栏目分类_8', 'name_en' => 'cat_8', 'parent_id' => 7),
    array('id' => 9, 'name' => '栏目分类_9', 'name_en' => 'cat_9', 'parent_id' => 6)
);
$objT = new TreeList($arrAll);
print_r($objT->arrAll);
print_r($objT->arrIdAll);
print_r($objT->arrIdChildren);
print_r($objT->arrIdLeaf);
print_r($objT->arrIdRelation);
print_r($objT->arrIdRelationSimple);
print_r($objT->arrIdRoot);
print_r($objT->arrIdBackPath);
print($objT->getTable());
print($objT->getSelect('cat', array(1, 8), true));
*/
class TreeList {
    
    /**
     * 分析出所有可能用到的数据
     */
    public $arrAll = array(); // 原始数据
    public $arrIdRelation = array(); // 按_ID作键名的多维关系
    public $arrIdRelationSimple = array(); //  按_ID作键名的多维关系的简化,用来输入树状图
    public $arrIdAll = array(); // 将原始数据转化成的_ID作键名的数组
    public $arrIdLeaf = array(); // 叶子节点的_ID
    public $arrIdRoot = array(); // 根节点的_ID
    public $arrIdChildren = array(); // 每个节点下的子孙_ID
    public $arrIdBackPath = array(); // 每个节点回逆到根
    public $strItem = '<br />{$strSep}{$name}'; // 输出树的结构
    
    /**
     * 构造函数,传入原始数据
     */
    public function __construct($arrData) {
        $this->arrAll = $arrData;
        $this->processData();
    }
    
    /**
     * 简单的树
     */
    public function getHtml() {
        return $this->genHtml();
    }
    
    /**
     * 用Table来画树
     */
    public function getTable() {
        $this->strItem = '<tr><td>{$strSep}{$name}</td><td align=\"center\">{$name}</td><td align=\"center\">{$name_en}</td></tr>';
        $strRe = '<table border="1" width="50%">';
        $strRe .= '<tr><th width="30%">结构</th><th width="20%">中文名</th><th width="10%">英文名</th></tr>';
        $strRe .= $this->genHtml();
        $strRe .= '</table>';
        return $strRe;
    }
    
    /**
     * 在下拉框中显示
     */
    public function getSelect($strName = 'tree', $arrValue = array(), $blmMulti = false) {
        !is_array($arrValue) && $arrValue = array($arrValue);
        foreach ($this->arrIdAll as $strTemp => $arrTemp) {
            $this->arrIdAll[$strTemp]['selected'] = '';
            if (in_array($arrTemp['id'], $arrValue)) {
                $this->arrIdAll[$strTemp]['selected'] = ' selected="selected"';
            }
        }
        $this->strItem = '<option value=\"{$id}\"{$selected}>{$strSep}{$name}({$name_en})</option>';
        return '<select ' . ($blmMulti ? 'multiple="multiple"' : '') . ' id="id_' . $strName . '" name="' . $strName . '">' . $this->getHtml() . '</select>';
    }
    
    /* ----- 以下的都是处理数据的私有函数,递归和循环之类,很复杂! ----- */
    private function helpForGetRelation($arrData) {
        $arrRe = array();
        foreach ($arrData as $strTemp => $arrTemp) {
            $arrRe[$strTemp] = $arrTemp;
            if (isset($this->arrIdRelation[$strTemp])) {
                $arrRe[$strTemp] = $this->arrIdRelation[$strTemp];
            }
            if (count($arrRe[$strTemp]) > 0) {
                $arrRe[$strTemp] = $this->helpForGetRelation($arrRe[$strTemp]);
            } else {
                array_push($this->arrIdLeaf, $strTemp);
            }
        }
        return $arrRe;
    }
    
    private function helpForGetChildren($arrData) {
        $arrRe = array_keys($arrData);
        foreach ($arrData as $arrTemp) {
            $arrRe = array_merge($arrRe, $this->helpForGetChildren($arrTemp));
        }
        return $arrRe;
    }
    
    private function helpForGetBackPath($str) {
        $arrRe = array();
        $intTemp = $this->arrIdAll[$str]['parent_id'];
        if ($intTemp > 0) {
            $intTemp = '_' . $intTemp;
            array_push($arrRe, $intTemp);
            $arrRe = array_merge($arrRe, $this->helpForGetBackPath($intTemp));
        }
        return $arrRe;
    }
    
    private function processData() {
        foreach ($this->arrAll as $arrTemp) {
            $strTemp = '_' . $arrTemp['id'];
            $this->arrIdAll[$strTemp] = $arrTemp;
            if ($arrTemp['parent_id'] > 0) {
                $strTemp_ = '_' . $arrTemp['parent_id'];
                !isset($this->arrIdRelation[$strTemp_]) && $this->arrIdRelation[$strTemp_] = array();
                $this->arrIdRelation[$strTemp_][$strTemp] = array();
            } else {
                array_push($this->arrIdRoot, $strTemp);
                $this->arrIdRelation[$strTemp] = array();
            }
        }
        $this->arrIdRelation = $this->helpForGetRelation($this->arrIdRelation);
        $this->arrIdLeaf = array_unique($this->arrIdLeaf);
        foreach ($this->arrIdRelation as $strTemp => $arrTemp) {
            $this->arrIdChildren[$strTemp] = $this->helpForGetChildren($arrTemp);
            in_array($strTemp, $this->arrIdRoot) && $this->arrIdRelationSimple[$strTemp] = $arrTemp;
        }
        $arrTemp = array_keys($this->arrIdAll);
        foreach ($arrTemp as $strTemp) {
            $this->arrIdBackPath[$strTemp] = $this->helpForGetBackPath($strTemp);
        }
    }
    
    private function genSeparator($intLen) {
        $strRe = '';
        $i = 0;
        while ($i < $intLen) {
            $strRe .= '　' . (($i + 1 == $intLen) ? '├' : '│');
            $i ++;
        }
        !empty($strRe) && $strRe .= '─';
        return $strRe;
    }
    
    private function genHtml($arrRelation = null, $intSep = 0) {
        $strRe = '';
        null === $arrRelation && $arrRelation = $this->arrIdRelationSimple;
        foreach ($arrRelation as $strKey => $arrTemp) {
            if (isset($this->arrIdAll[$strKey]) && is_array($this->arrIdAll[$strKey]) && count($this->arrIdAll[$strKey]) > 0) {
                $strSep = $this->genSeparator($intSep);
                extract($this->arrIdAll[$strKey]);
                eval('$strRe .= "' . $this->strItem . '";');
                is_array($arrTemp) && count($arrTemp) > 0 && $strRe .= $this->genHtml($arrTemp, ($intSep + 1));
            }
        }
        return $strRe;
    }
}
