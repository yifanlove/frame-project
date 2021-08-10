<?php
namespace Lib\Facade;

use Lib\Facade;

class Route extends Facade
{
    protected static function getFacadeClass()
    {
        return 'route';
    }
}