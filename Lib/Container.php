<?php
namespace Lib;
use ArrayAccess;

use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use think\Exception;
use think\exception\ClassNotFoundException;

    class Container implements ArrayAccess
{
    /**
     * 容器对象实例
     * @var Container
     */
    protected static $instance;
    /**
     * 容器中的对象实例
     * @var array
     */
    protected $instances=[];
    /**
     * 容器绑定标识
     * @var array
     */
    protected $bind = [
        'app'=>App::class,
        'route'=>Route::class,
        'redis'=>Redis::class
    ];

    /**
     * @return mixed
     */
//    public static function getInstance()
//    {
//        // 判断容器对象是否实例，为空则实例化当前容器对象，赋值给$instance,不为空则直接返回$instance
//        if(is_null(static::$instance)){
//            static::$instance = new static();
//        }
//        return static::$instance;
//    }
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        return static::$instance;
    }
    /**
     * 绑定一个类到容器
     * @param $abstract 类标识、接口
     * @param null $concrete 要绑定的类实例
     * @return $this
     */
    public static function set($abstract,$concrete=null)
    {
        return static::getInstance()->bindTo($abstract,$concrete);
    }

    /**
     * 从容器获取对象实例
     * @param $abstract         类名或标识
     * @param array $vars       参数
     * @param bool $newInstance 是否每次创建新的实例
     * @return mixed
     */
    public static function get($abstract, $vars = [], $newInstance = false)
    {
        return static::getInstance()->make($abstract,$vars,$newInstance);
    }

    /**
     * 移除容器的对象实例
     * @param $abstract     类名或标识
     * @return mixed
     */
    public static function remove($abstract)
    {
        return static::getInstance()->delete($abstract);
    }

    /**
     * 创建类的实例
     * @param $abstract         类名或标识
     * @param array $vars       参数
     * @param bool $newInstance 是否每次创建新的实例
     * @return mixed|object
     */
    public function make($abstract,$vars = [],$newInstance=false)
    {
        if (true === $vars) {
            // 总是创建新的实例化对象
            $newInstance = true;
            $vars        = [];
        }
        // 判断容器中是否有对象实例并且不创建新的实例
        if (isset($this->instances[$abstract]) && !$newInstance) {
            // 从容器中取出对象实例
            $object = $this->instances[$abstract];
        } else {
            // 判断是否在容器标识中
            if (isset($this->bind[$abstract])) {
                $concrete = $this->bind[$abstract];
                $object = $this->make($concrete, $vars, $newInstance);
            } else {
                // 利用反射机制实例化类
                $object = $this->invokeClass($abstract, $vars);
            }
            if (!$newInstance) {
                $this->instances[$abstract] = $object;
            }
        }

        return $object;
    }

    /**
     * 绑定一个类到容器
     * @param $abstract 类标识、接口
     * @param null $concrete 要绑定的类实例
     * @return $this
     */
    public function bindTo($abstract,$concrete=null)
    {
        // 要绑定的类实例是否为obj，true则将类实例放入容器中，false则放入类标识中
        if(is_object($concrete)){
            $this->instances[$abstract]=$concrete;
        }else{
            $this->bind[$abstract]=$concrete;
        }

        return $this;
    }
    /**
     * 调用反射执行类的方法 支持参数绑定
     * @access public
     * @param  object  $instance 对象实例
     * @param  mixed   $reflect 反射类
     * @param  array   $vars   参数
     * @return mixed
     */
    public function invokeReflectMethod($instance, $reflect, $vars = [])
    {
        $args = $this->bindParams($reflect, $vars);

        return $reflect->invokeArgs($instance, $args);
    }
    /**
     * 调用反射执行类的实例化 支持依赖注入
     * @param string $class        类标识
     * @param array $vars   变量
     * @return object
     * @throws \ReflectionException
     */
    public function invokeClass($class,$vars = [])
    {
        try{
            $reflect = new ReflectionClass($class);

            if ($reflect->hasMethod('__make')) {
                $method = new ReflectionMethod($class, '__make');

                if ($method->isPublic() && $method->isStatic()) {
                    $args = $this->bindParams($method, $vars);
                    return $method->invokeArgs(null, $args);
                }
            }

            $constructor = $reflect->getConstructor();
            $args = $constructor?$this->bindParams($constructor,$vars) : [];
            return $reflect->newInstanceArgs($args);
        }catch (Exception $e){
            throw new ClassNotFoundException('class not exists: ' . $class, $class);
        }
    }

    /**
     * @param $reflect
     * @param array $vars
     * @return array|mixed
     */
    public function bindParams($reflect,$vars = [])
    {
        // 先判断这个反射类反射的方法是否含有参数，没有就直接返回空数组
        if($reflect->getNumberOfParameters()==0){
            return [];
        }
        // 用reset保证数组$vars指针指向第一个元素，判断该数组的类型，数字数组type为1，反之为2
        reset($vars);
        $type = key($vars) === 0 ? 1 : 0;
        // 通过getParameters获取参数赋值给$params
        $params = $reflect->getParameters();
        // 遍历$params获取其参数名称和转为c语言风格的参数名称
        foreach ($params as $param){
            $class     = $param->getClass();
            if($class){
                $args[] = $this->getObjectParam($class->getName(), $vars);
            }elseif($type==1 && !empty($vars)){
                $args[] = array_shift($vars);
            }else{
                //                若都没有，则说明参数缺失，抛出参数缺失的异常
                throw new \InvalidArgumentException('method param miss');
            }
        }

        return  $args;
    }

    public function getObjectParam($className,&$vars)
    {
        $array = $vars;
        $value = array_shift($array);

        if ($value instanceof $className) {
            $result = $value;
            array_shift($vars);
        } else {
            $result = $this->make($className);
        }

        return $result;
    }

    /**
     * 删除容器中的对象实例
     * @param $abstract 类名或标识
     */
    public function delete($abstract)
    {
        foreach ((array)$abstract as $item){
            if (isset($this->instances[$item])){
                unset($this->instances[$item]);
            }
        }
    }

    public function __set($name, $value)
    {
        $this->bindTo($name, $value);
    }

    public function __get($name)
    {
        return $this->make($name);
    }

    public function __unset($name)
    {
        return $this->delete($name);
    }

    public function offsetExists($offset)
    {

    }

    public function offsetGet($offset)
    {
        $this->__get($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->__set($offset);
    }

    public function offsetUnset($offset){
        $this->__unset($offset);
    }
}
