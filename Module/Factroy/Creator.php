<?php
namespace Module\Factroy;

/**
 * Interface Creator
 * @package Module\Factroy
 * 抽象工厂
 */
interface Creator
{
    function Car();
    function Bike();
}