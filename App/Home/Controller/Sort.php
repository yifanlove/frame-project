<?php
namespace App\Home\Controller;

use Extend\Redis\GoodsCart;
use Extend\Redis\RedisLimit;
use Lib\Facade\Redis;

class Sort
{
    public function quick(){
        $arr = [2,9,5,6,10,8,4,3,7,1];
        $len = count($arr);
        $arr1 = $this->quickSort($arr,0,$len-1);
        var_dump($arr1);

    }

    /**
     * 快速排序
     * @param $arr
     * @param $leftBound
     * @param $rightBoubd
     */
    function quickSort(&$arr,$leftBound,$rightBoubd){

        if($leftBound>=$rightBoubd)return;

        $pvoit = $arr[$rightBoubd];
        $left = $leftBound;
        $right = $rightBoubd-1;


        while ($left<=$right){
            while ($left<=$right&&$arr[$left]<=$pvoit)$left++;
            while ($left<=$right&&$arr[$right]>$pvoit)$right--;
            if($left<$right) {
                $temp=$arr[$left];
                $arr[$left]=$arr[$right];
                $arr[$right]=$temp;
            }

        }
        //交换位置
        if($arr[$rightBoubd]<$arr[$left]){
            $temp = $arr[$left];
            $arr[$left] = $arr[$rightBoubd];
            $arr[$rightBoubd] = $temp;
        }
        // 左边排序
        $this->quickSort($arr,$leftBound,$left-1);
        // 右边排序
        $this->quickSort($arr,$left+1,$rightBoubd);

        return $arr;

    }

    public function count(){
        $arr = [5,3,3,4,1,6,1,7,5,4,7,8,4,1,1,0,3,0,2];
        $len = count($arr);
        $maxValue=$arr[0];
        // 寻找最大值
        for ($i=0;$i<$len-1;$i++){
            if ($arr[$i]>$maxValue){
                $maxValue = $arr[$i];
            }
        }
        // 申请定长数组
        $frequency = new \SplFixedArray($maxValue + 1);
        for ($i=0;$i<$len;$i++) {
            if(empty($frequency[$arr[$i]]))
                $frequency[$arr[$i]] = 0;
            $frequency[$arr[$i]] += 1;
        }
//        for ($i=0;$i<count($frequency);$i++) {
//            while($frequency[$i]>0){
//                $result[]=$i;
//                $frequency[$i]--;
//            }
//        }
        for ($i=0; $i<count($frequency); $i++) {
            if (!empty($frequency[$i])) {
                for ($j=0;$j<$frequency[$i];$j++) {
                    var_dump($j);
                    $result [] = $i;
                }
    }
        }
//        var_dump($frequency);
//        var_dump($result);
    }

}
