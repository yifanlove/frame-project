<?php


namespace Module\Factroy;

/**
 * Class BenzFactroy
 * @package Module\Factroy
 * 具体工厂
 */
class BenzFactroy implements Creator
{
    public function Bike()
    {
        // TODO: Implement Bike() method.
        return new Giant();
    }

    public function Car()
    {
        // TODO: Implement Car() method.
        return new Benz();
    }
}