<?php


namespace Module\Decorate;


class House extends Land
{
    public function house($rooms='建起了一座小洋房',$money=100000){
        $this->House_rooms[] = "$rooms";
        $this->money=$this->money+$money;

        $this->Decorate();
    }
}