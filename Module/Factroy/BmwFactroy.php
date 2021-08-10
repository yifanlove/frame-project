<?php


namespace Module\Factroy;


class BmwFactroy implements Creator
{
    public function Bike()
    {
        // TODO: Implement Bike() method.
        return new Merida();
    }

    public function Car()
    {
        // TODO: Implement Car() method.
        return new Bmw();
    }
}