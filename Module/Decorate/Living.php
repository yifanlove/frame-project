<?php


namespace Module\Decorate;


class Living implements HouseDecorate
{
    public function cost()
    {
        // TODO: Implement cost() method.
        $money = 50000;
        return $money;
    }

    public function build(){
        $build = "装修了一个客厅";
        return $build;
    }
}