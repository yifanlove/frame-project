<?php


namespace Module\Observer;


class Event implements Observer
{
    public function handle()
    {
        // TODO: Implement handle() method.
        echo "登陆事件!<br/>";
    }
}