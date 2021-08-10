<?php
namespace Extend;

class Worker{

    public $onMessage;// 绑定事件回调事件
    protected $server;

    public function __construct($address)
    {
        $this->server=stream_socket_server($address);
    }

    public function runAll(){
        // 监听服务端socket，当服务端可读的时候触发
        swoole_event_add($this->server,function ($fd){
            $cilentSocket=stream_socket_accept($this->server);// 阻塞获取客户端的fd
            // 当客户端状态发生改变时触发（数据发送的时候）
            swoole_event_add($cilentSocket,function ($fd){
                if(feof($fd) || !is_resource($fd)){
                    // 删除事件
                    swoole_event_del($fd);
                    // 触发onClose事件
                    fclose($fd);
                    return null;
                }
                $message = fread($fd,65535);
                if(is_callable($this->onMessage)){
                    call_user_func($this->onMessage,$this,$fd,$message);
                }
            });
        });
        var_dump($this->server);
        while (true){
            // 从fd当中读取客户端的信息

        }
    }

    /**
     * 发送信息方法
     * @param $fd
     * @param $message
     */
    public function send($fd,$message){
        // 发送信息到客户端
        fwrite($fd,$message);
    }
}