<?php

namespace Lib;

use Module\Factroy\Benz;
use think\Exception;

class Route
{
    protected $rules = [];

    protected $domain;
    /**
     * 当前路由
     * @var
     */
    protected $route;
    /**
     * 当前路由规则
     * @var
     */
    protected $rule;

    protected $perfix = 'Home';

    public function __construct()
    {
        $this->getRule();
    }


    public function getRule()
    {
        $this->rule = '/' != $_SERVER['REQUEST_URI'] ? ltrim($_SERVER['REQUEST_URI'], '/') : '';
        if (!$this->rule) {
            $this->rule = '/';
        }
    }

    public function addRule($rule, $route, $perfix = '')
    {
        $rule = '/' != $rule ? ltrim($rule, '/') : '';
        if (!array_key_exists($rule, $this->rules)) {
            $route = explode('/', $route);
            $rules[$rule] = $route;
            if ($perfix) {
                $this->rules[$perfix] = $rules;
            } else {
                $this->rules[$this->perfix] = $rules;
            }
        } else {
            throw new \Exception("请不要设置相同的路由规则");
        }
    }

    public function check()
    {
        if ($this->rule != '/') {
            $rule = explode('/', $this->rule);
            $first = ucfirst($rule[0]);
            if (array_key_exists($first,$this->rules)) {
                $rules = $this->rules[$first];
                $perfix = $first;
                $this->rule = str_replace("$rule[0]/",'',$this->rule);
            } else {
                $rules = $this->rules[$this->perfix];
                $perfix = $this->perfix;
            }
            if (array_key_exists($this->rule, $rules)) {
                $route = $rules[$this->rule];
                return $this->parseName($route,$perfix);
            } else {
                throw new \Exception('路由不存在');
            }
        } else {
            return $this->parseName(['index','index'],$this->perfix);
        }
    }

    public function parseName($route,$perfix)
    {
        $rule['controller'] = "App\\"."$perfix\\"."Controller\\".ucfirst($route[0]);
        $rule['action']  = $route[1];
        return $rule;
    }

}