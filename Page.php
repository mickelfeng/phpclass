/**
* 获得页区间页号
*
* @param int $currentPage 当前页号
* @param int $totalPages 总页数
* @param int $displaySize 区间容量,默认显示10页
* @return array 返回由区间页号组成的数组
*/
function getPageRange($currentPage, $totalPages, $displaySize = 10) {
    if ($totalPages <= 0 || $displaySize <= 0) {
        return array();
    } elseif ($displaySize > $totalPages) {
        $startPage = 1;
        $endPage = $totalPages;
    } else {
        if ($currentPage % $displaySize === 0) {
            $startPage = $currentPage – $displaySize + 1;
        } else {
            while (($currentPage % $displaySize)) {
                –$currentPage;
            }
            $startPage = $currentPage + 1;
        }
        if ($startPage <= 0) {
            $startPage = 1;
        }
        $endPage = $startPage + $displaySize – 1;
        if ($endPage > $totalPages) {
            $endPage = $totalPages;
            $startPage = $endPage – $displaySize + 1;
        }
    }
return range($startPage, $endPage);
