<?php
namespace App\Home\Controller;


use Extend\Redis\GoodsCart;
use Extend\Redis\RedisLimit;
use Lib\Facade\Redis;

class Index
{

    public function index()
    {
//       $dr=opendir("/www/wwwroot/project");
//
//       while (false!==($file = readdir($dr))){
//           if($file!='.' && $file !='..'){
//               if(is_dir($file) ){
//                   $files[$file] = scandir($file);
//               }
//               else{
//                   $files[] = $file;
//               }
//           }
//
//       }
//var_dump($files);
        $b = array( 'one' );
        $b[] =& $b;
        xdebug_debug_zval( 'b' );
    }
    public function cart(){
        $userId = 10001;
        $goodsId = 1;
        $info = array(
            'name'=>'三级总承包',
            'price'=>60000.00
        );
        $num = 2;
        $pre = 'goods_';
        $cart = new GoodsCart($userId,$goodsId,$info,$num,$pre);
//        $cart->insertCart();
        $hash = $cart->getGoodsValue();
        var_dump($hash);

    }
    public function build(){
        $land =  new House();
//        $room->addDecorate(new Living());
        $room = new Room();
       for($i=0;$i<17;$i++){
           $land->addDecorate($room);
       }
        $land->house();
    }

    public function buy(){
        // 启动http服务，开放9091端口
        $woker = new Worker("tcp://0.0.0.0:9503");

        $woker->runAll();

    }

    public function limit()
    {
//        $json = RedisLimit::getRedisConn()->get(md5('test'));
        $json = RedisLimit::lock_interface_limit("test");
        var_dump($json);


    }
}
