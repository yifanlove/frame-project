<?php


namespace Module\Observer;


class Paper
{
    private $Observers = [];
    public function register(Observer $observer){
        $this->Observers[] = $observer;
    }

    public function notify(){
        foreach ($this->Observers as $observer){
            $observer->handle();
        }
    }
}