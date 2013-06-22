<?php 
    /** 
     * KMP算法的PHP实现 
     * 
     * @author zhaojiangwei 2011/10/22 10:28 
     */ 

    class KMP{ 
        private $next = NULL; //模式串T的next数组 
        private $t = NULL; //模式串 
        private $str = NULL; //主串 

        public function KMP($str){ 
            $this->str = str_split($str); 
            $this->next = array(); 
        } 

        //返回主串的长度 
        public function getStrCount(){ 
            return count($this->str); 
        } 

        //返回结果 
        public function getStrPos($substr){ 
            $substr = str_split($substr); 
            $this->getNext($substr); 
            $strCount = $this->getStrCount(); 
            $substrCount = count($substr); 
            $subIndex = 0;//子串的起始比较位置 
            $strIndex = 0;//主串目前的比较到的位置 

            while($subIndex < $substrCount && ($strCount - $strIndex) >= ($substrCount - $subIndex)){ 
                if($subIndex == -1 || $this->str[$strIndex] == $substr[$subIndex]){ 
                    $subIndex ++; 
                    $strIndex ++; 
                }else{ 
                    $subIndex = $this->next[$subIndex]; 
                } 
            } 

            if($subIndex == $substrCount){ 
                return $strIndex - $substrCount; 
            }else{ 
                return false; 
            } 
         } 

         //求模式串的NEXT数组 
         public function getNext($t){ 
            if(!is_array($t)){ 
                $t = str_split($t); 
            } 

            $this->next[0] = -1; 
            $count = count($t); 

            $i = 0; 
            $j = -1; 
            while($i < $count){ 
                if($j == -1 || $t[$i] == $t[j]){ 
                    $j ++; 
                    $i ++; 
                    
                    if($t[$i] == $t[$j]){ 
                        $this->next[$i] = $this->next[$j]; 
                    }else{ 
                        $this->next[$i] = $j; 
                    } 
                }else{ 
                    $j = $this->next[$j]; 
                } 
            } 

            return $this->next; 
        } 

   } 
