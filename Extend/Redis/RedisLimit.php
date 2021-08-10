<?php

namespace Extend\Redis;


/**
 * 利用Redis实现简单接口限流
 * Class RedisLimit
 * @package Extend\Redis
 */
class RedisLimit
{
    /**
     * 单位时间 （秒）
     * @var int
     */
    static $Time = 60;
    /**
     * 接口最大访问次数
     * @var int
     */
    static $InterFaceMax = 5000;
    /**
     * 单位时间接口最大访问次数
     * @var int
     */
    static $InterFaceTimeMax = 5;
    /**
     * 每个用户最大访问次数
     * @var int
     */
    static $UserMax = 50;
    /**
     * 单位时间，每个用户最大访问次数
     * @var int
     */
    static $UserTimeMax = 10;
    /**
     * ip最大访问次数
     * @var int
     */
    static $IpMax = 100;
    /**
     * 单位时间,ip最大访问次数
     * @var int
     */
    static $IpTimeMax = 20;
    private static $conn;
    private static $host = '127.0.0.1';
    private static $port = 6379;

    public function __construct()
    {
    }

    public static function lock_interface_limit($key)
    {
        $key = md5($key);
        // 获取redis实例
        $redis = static::getRedisConn();
        // 获取key的值
        $interfaceMax = $redis->get($key);
        // 对比key的值，大于接口最大访问，则返回
        if ($interfaceMax) {
            if ($interfaceMax >= static::$InterFaceMax) return ['code' => 0, 'msg' => '该接口已到达访问最大访问次数，加钱'];
        }
        $interfaceTimeMax = static::siled($key);
        if ($interfaceTimeMax >= static::$InterFaceTimeMax) return ['code' => 0, 'msg' => '访问频繁，请稍后重试'];
        // 接口访问次数++1
        $redis->incr($key);

        return ['code' => 1, 'msg' => '通过'];
    }

    public static function lock_user_limit($userId)
    {
        $key = 'user_' . $userId;
        $redis = self::getRedisConn();
        $userMax = $redis->get($key);
        if ($userMax >= static::$userMax) return ['code' => 0, 'msg' => "你的调用次数达到上限，请购买会员"];
        $userTimeMax = static::siled($key);
        if ($userTimeMax >= static::$userTimeMax) return ['code' => 0, 'msg' => "访问频繁，请稍后重试"];
        $redis->incr($key);
        return ['code' => 1, 'msg' => "通过"];
    }

    public static function lock_ip_limit()
    {
        $key = 'ip_' . $_SERVER['REMOTE_ADDR'];
        $redis = self::getRedisConn();
        $userMax = $redis->get($key);
        if ($userMax >= static::$userMax) return ['code' => 0, 'msg' => "你的调用次数达到上限，请购买会员"];
        $userTimeMax = static::siled($key);
        if ($userTimeMax >= static::$userTimeMax) return ['code' => 0, 'msg' => "访问频繁，请稍后重试"];
        $redis->incr($key);
        return ['code' => 1, 'msg' => "通过"];
    }

    private static function siled($key)
    {
        $score = time();
        $siledKey = $key . '_' . $score;
        $redisKey = $key . '_' . static::$Time;
        // 开启事务
        static::$conn->multi();
        // 删除窗口以外的数据（小于score60的则在窗口外）
        static::$conn->zRemRangeByScore($redisKey, 0, $score - static::$Time);
        static::$conn->zAdd($redisKey, $score, $siledKey);
        // 设置窗口大小
        static::$conn->expire($redisKey, static::$Time);
        // 统计窗口内的个数
        static::$conn->zRange($redisKey, 0, -1, true);
        $num = static::$conn->exec();
        return count($num[3]);
    }

    /**
     * @return mixed
     */
    public static function getRedisConn()
    {
        if (!static::$conn) {
            static::$conn = new \Redis();
            static::$conn->connect(static::$host, static::$port);
            static::$conn->auth('redis');
        }
        return static::$conn;
    }
}