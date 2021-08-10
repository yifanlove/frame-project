<?php
namespace Extend\Redis;

use Lib\Facade\Redis;
use mysql_xdevapi\Exception;

/**
 *
 *
 */
class SecKill
{
    protected $LockKeyPrefix = "seckill_";
    /**
     * 预热缓存，防止缓存击穿
     * 数组可以批量缓存
     * @param $goods_id
     * @param $num
     */
    public function preheat($goods_id,$num){
        try{
            if(is_array($goods_id)){
                Redis::mset($goods_id);
            }else{
                Redis::set($goods_id,$num);
            }
        }catch (Exception $e){

        }
    }

    /**
     * 检查缓存库存
     * @param $goods_id
     * @return bool
     */
    public function checkCache($goods_id){
        try{
            $num = Redis::get($goods_id);
        }catch (Exception $exception){

        }
        if($num>0){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 获取锁的key
     * @param $goods_id
     * @return string
     */
    protected function getLockKey($goods_id){
        return $this->LockKeyPrefix.$goods_id;
    }
    /**
     * 获取分布式锁
     * @param $lockKey
     * @param $requestId
     * @param $expireTime
     * @param int $timeout
     * @return bool
     */
    public function getLock($lockKey,$requestId,$expireTime,$timeout=500){
        try{
            $start = $this->msectime();
            while (true){
                $lock = Redis::set($lockKey,$requestId,"NX","PX",$expireTime);
                if (!empty($lock)){
                    return true;
                }
                $time = $this->msectime()-$start;
                if($time>=$timeout){
                    return false;
                }
                sleep(50);
            }
        }catch (Exception $exception){

        }
        return false;
    }

    /**
     * 获取自旋锁开始时间
     * @return float
     */
    public  function msectime() {
       $timeArr = explode('',microtime());
       $time = (float)sprintf('%.0f', (floatval($timeArr[0]) + floatval($timeArr[1])) * 1000);
       return $time;
    }

    public function checkDatabaseStock(){

    }
}
