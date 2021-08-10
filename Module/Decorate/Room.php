<?php

namespace Module\Decorate;


class Room implements HouseDecorate
{
    public function cost()
    {
        // TODO: Implement cost() method.
        $money = 20000;
        return $money;
    }

    public function build(){
        $build = "装修了一间主卧";
        return $build;
    }
}