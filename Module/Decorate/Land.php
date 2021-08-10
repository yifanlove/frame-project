<?php


namespace Module\Decorate;



class Land
{
    private $money = 0;
    private $House_rooms = [];
    private $Decorates=[];

    public function cost()
    {
        // TODO: Implement cost() method.
        foreach ($this->Decorates as $decorate){
            $this->money =$this->money+$decorate->cost();
        }
        return $this->money;
    }

    public function build(){
        foreach ($this->Decorates as $decorate){
            $this->House_rooms[] = $decorate->build();
        }
        return $this->House_rooms;
    }
    public function addDecorate(HouseDecorate $decorate){
        $this->Decorates[] = $decorate;
    }

    public function Decorate(){
        $builds = $this->build();
        foreach ($builds as $build){
            echo $build."<br/>";
        }
        echo "总花费：".$this->cost();
    }

//    public function house($rooms='建起了一座小洋房',$money=100000){
//        $this->House_rooms[] = "$rooms";
//        $this->money=$this->money+$money;
//
//        $this->Decorate();
//    }
}