<?php
namespace Lib\Facade;

use Lib\Facade;

class Redis extends Facade
{
    protected static function getFacadeClass()
    {
        return 'redis';
    }
}
