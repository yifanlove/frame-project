<?php
use Lib\Container;
define('BASEDIR',__DIR__.'/');
include BASEDIR.'/Lib/Loader.php';
spl_autoload_register('\\Lib\\Loader::autoload');

Container::get('app')->run();





