<?php

namespace Lib;

use Lib\Facade\Route;

class App  extends Container
{
    /**
     * 应用根目录
     * @var string
     */
    protected $rootPath;
    /**
     * 路由目录
     * @var string
     */
    protected $routePath;
    /**
     * 初始化
     * @var bool
     */
    protected $initialized = false;

    public function initialize()
    {
        if ($this->initialized) {
            return;
        }

        $this->rootPath    = dirname(__DIR__) . DIRECTORY_SEPARATOR;
        $this->routePath   = $this->rootPath . 'Route' . DIRECTORY_SEPARATOR;

        $this->routeInit();
    }
    public function run()
    {
        $this->initialize();

        $rule = Route::check();
        $instance = $this->make($rule['controller']);
        $reflect = new \ReflectionMethod($instance,$rule['action']);
        $this->invokeReflectMethod($instance,$reflect);
    }

    public function routeInit()
    {
        // 路由检测
        if (is_dir($this->routePath)) {
            $files = glob($this->routePath . '*.php');

            foreach ($files as $file) {
                include $file;
            }
        }
    }
}
