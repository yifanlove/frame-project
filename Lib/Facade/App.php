<?php
namespace Lib\Facade;

use Lib\Facade;

class App extends Facade
{
    protected static function getFacadeClass()
    {
        return 'app';
    }
}