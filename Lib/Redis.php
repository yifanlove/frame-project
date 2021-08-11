<?php

 namespace Lib;
 /**
  * Class Redis
  * @package Lib
  */
 class Redis
 {
     /**
      * @var
      */
     protected $client;

     protected  $host = '127.0.0.1';
     protected  $port = 6379;
     /**
      * Redis constructor.
      * @param $client
      */
     public function __construct()
     {
         $this->getIntsance();
     }

     /**
      * 获取Redis实例（单例）
      * @return \Redis
      */
     public  function getIntsance(){
         if (!$this->client) {
             $this->client = new \Redis();
             $this->client->connect($this->host, $this->port);
             $this->client->auth('redis');
         }
         return $this->client;
     }

     /**
      * 返回给定key的值
      *
      * @param  string  $key
      * @return string|null
      */
     public function get($key)
     {
         $result = $this->client->get($key);

         return $result !== false ? $result : null;
     }

     /**
      * 获取所有给定key的值。
      *
      * @param  array  $keys
      * @return array
      */
     public function mget(array $keys)
     {
         return array_map(function ($value) {
             return $value !== false ? $value : null;
         }, $this->client->mget($keys));
     }

     /**
      * 确定给定的key是否存在。
      *
      * @param  dynamic  $keys
      * @return int
      */
     public function exists(...$keys)
     {
         $keys = collect($keys)->map(function ($key) {
             return $this->applyPrefix($key);
         })->all();

         return $this->executeRaw(array_merge(['exists'], $keys));
     }

     /**
      * 设置string
      *
      * @param  string  $key
      * @param  mixed  $value
      * @param  string|null  $expireResolution
      * @param  int|null  $expireTTL
      * @param  string|null  $flag
      * @return bool
      */
     public function set($key, $value, $expireResolution = null, $expireTTL = null, $flag = null)
     {
         return $this->command('set', [
             $key,
             $value,
             $expireResolution ? [$flag, $expireResolution => $expireTTL] : null,
         ]);
     }

     /**
      * 如果给定的键不存在，请设置该键。
      *
      * @param  string  $key
      * @param  string  $value
      * @return int
      */
     public function setnx($key, $value)
     {
         return (int) $this->client->setnx($key, $value);
     }

     /**
      * 批量获取hsah.
      *
      * @param  string  $key
      * @param  dynamic  $dictionary
      * @return int
      */
     public function hmget($key, ...$dictionary)
     {
         if (count($dictionary) == 1) {
             $dictionary = $dictionary[0];
         }

         return array_values($this->command('hmget', [$key, $dictionary]));
     }

     /**
      * 批量设置hsah
      *
      * @param  string  $key
      * @param  dynamic  $dictionary
      * @return int
      */
     public function hmset($key, ...$dictionary)
     {
         if (count($dictionary) == 1) {
             $dictionary = $dictionary[0];
         } else {
             $input = collect($dictionary);

             $dictionary = $input->nth(2)->combine($input->nth(2, 1))->toArray();
         }

         return $this->command('hmset', [$key, $dictionary]);
     }

     /**
      * 如果给定的哈希字段不存在，则设置该字段。
      *
      * @param  string  $hash
      * @param  string  $key
      * @param  string  $value
      * @return int
      */
     public function hsetnx($hash, $key, $value)
     {
         return (int) $this->client->hSetNx($hash, $key, $value);
     }

     /**
      * 从列表中删除值元素的首次计数
      *
      * @param  string  $key
      * @param  int  $count
      * @param  $value  $value
      * @return int|false
      */
     public function lrem($key, $count, $value)
     {
         return $this->command('lrem', [$key, $value, $count]);
     }

     /**
      *
      *
      * @param  string  $key
      * @param  int|null  $count
      * @return mixed|false
      */
     public function spop($key, $count = null)
     {
         return $this->command('spop', [$key]);
     }

     /**
      * 将一个或多个成员添加到集合中或更新其分数
      *
      * @param  string  $key
      * @param  dynamic  $dictionary
      * @return int
      */
     public function zadd($key, ...$dictionary)
     {
         if (is_array(end($dictionary))) {
             foreach (array_pop($dictionary) as $member => $score) {
                 $dictionary[] = $score;
                 $dictionary[] = $member;
             }
         }

         $key = $this->applyPrefix($key);

         return $this->executeRaw(array_merge(['zadd', $key], $dictionary));
     }

     /**
      * 按分数返回集合中的元素
      *
      * @param  string  $key
      * @param  mixed  $min
      * @param  mixed  $max
      * @param  array  $options
      * @return int
      */
     public function zrangebyscore($key, $min, $max, $options = [])
     {
         if (isset($options['limit'])) {
             $options['limit'] = [
                 $options['limit']['offset'],
                 $options['limit']['count'],
             ];
         }

         return $this->command('zRangeByScore', [$key, $min, $max, $options]);
     }

     /**
      * 返回得分在$min和$max之间的元素
      *
      * @param  string  $key
      * @param  mixed  $min
      * @param  mixed  $max
      * @param  array  $options
      * @return int
      */
     public function zrevrangebyscore($key, $min, $max, $options = [])
     {
         if (isset($options['limit'])) {
             $options['limit'] = [
                 $options['limit']['offset'],
                 $options['limit']['count'],
             ];
         }

         return $this->command('zRevRangeByScore', [$key, $min, $max, $options]);
     }

     /**
      * 找到集合之间的交集并存储在新集合中。
      *
      * @param  string  $output
      * @param  array  $keys
      * @param  array  $options
      * @return int
      */
     public function zinterstore($output, $keys, $options = [])
     {
         return $this->zInter($output, $keys,
             $options['weights'] ?? null,
             $options['aggregate'] ?? 'sum'
         );
     }

     /**
      * 查找集合之间的并集并存储在新集合中。
      *
      * @param  string  $output
      * @param  array  $keys
      * @param  array  $options
      * @return int
      */
     public function zunionstore($output, $keys, $options = [])
     {
         return $this->zUnion($output, $keys,
             $options['weights'] ?? null,
             $options['aggregate'] ?? 'sum'
         );
     }

     /**
      * 在管道中执行命令
      *
      * @param  callable  $callback
      * @return \Redis|array
      */
     public function pipeline(callable $callback = null)
     {
         $pipeline = $this->client()->pipeline();

         return is_null($callback)
             ? $pipeline
             : tap($pipeline, $callback)->exec();
     }

     /**
      * 在事务中执行命令。
      *
      * @param  callable  $callback
      * @return \Redis|array
      */
     public function transaction(callable $callback = null)
     {
         $transaction = $this->client()->multi();

         return is_null($callback)
             ? $transaction
             : tap($transaction, $callback)->exec();
     }





     /**
      * 执行原始命令
      *
      * @param  array  $parameters
      * @return mixed
      */
     public function executeRaw(array $parameters)
     {
         return $this->command('rawCommand', $parameters);
     }

     /**
      * Disconnects from the Redis instance.
      *
      * @return void
      */
     public function disconnect()
     {
         $this->client->close();
     }

     /**
      * Apply prefix to the given key if necessary.
      *
      * @param  string  $key
      * @return string
      */
     private function applyPrefix($key)
     {
         $prefix = (string) $this->client->getOption(Redis::OPT_PREFIX);

         return $prefix.$key;
     }

     /**
      * Run a command against the Redis database.
      *
      * @param  string  $method
      * @param  array   $parameters
      * @return mixed
      */
     public function command($method, array $parameters = [])
     {
         return $this->client->{$method}(...$parameters);
     }

     /**
      * Pass other method calls down to the underlying client.
      *
      * @param  string  $method
      * @param  array  $parameters
      * @return mixed
      */
     public function __call($method, $parameters)
     {
         return $this->command($method, $parameters);
     }
 }
