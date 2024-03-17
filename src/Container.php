<?php
/*
 *  +----------------------------------------------------------------------
 *  | ViSwoole [基于swoole开发的高性能快速开发框架]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2024
 *  +----------------------------------------------------------------------
 *  | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
 *  +----------------------------------------------------------------------
 *  | Author: ZhuChongLin <8210856@qq.com>
 *  +----------------------------------------------------------------------
 */

declare (strict_types=1);

namespace ViSwoole\Core;

use ArrayAccess;
use ArrayIterator;
use Closure;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionNamedType;
use ReturnTypeWillChange;
use ViSwoole\Core\Common\Str;
use ViSwoole\Core\Exception\ContainerException;
use ViSwoole\Core\Exception\FuncNotFoundException;

/**
 * 容器与依赖注入类
 */
class Container implements ContainerInterface, ArrayAccess, IteratorAggregate, Countable
{
  /**
   * 容器对象实例
   * @var Container
   */
  protected static Container $instance;

  /**
   * 容器中的持久化对象实例
   * @var array
   */
  protected array $instances = [];

  /**
   * 容器绑定标识
   * @var array
   */
  protected array $binds = [];

  /**
   * 获取当前容器的实例（单例）
   * @access public
   * @return static
   */
  public static function getInstance(): static
  {
    if (!isset(static::$instance)) {
      static::$instance = new static;
    }

    if (static::$instance instanceof Closure) {
      return (static::$instance)();
    }
    return static::$instance;
  }

  /**
   * 设置当前容器的实例
   * @access public
   * @param Container $instance
   * @return void
   */
  public static function setInstance(Container $instance): void
  {
    static::$instance = $instance;
  }

  /**
   * 调用反射执行类的方法 支持参数绑定
   * @access public
   * @param object|null $instance 对象实例 静态对象
   * @param ReflectionMethod $reflect 反射类
   * @param array $vars 参数
   * @return mixed
   * @throws ReflectionException
   */
  public function invokeReflectMethod(?object $instance, ReflectionMethod $reflect, array $vars = []
  ): mixed
  {
    $args = $this->bindParams($reflect, $vars);
    return $reflect->invokeArgs($instance, $args);
  }

  /**
   * 绑定参数
   * @param ReflectionFunctionAbstract $reflect 反射类
   * @param array $vars 参数
   * @return array
   * @throws ReflectionException
   */
  protected function bindParams(ReflectionFunctionAbstract $reflect, array $vars = []): array
  {
    //通过 getNumberOfParameters() 方法检查参数的数量，如果没有参数，则直接返回一个空数组
    if ($reflect->getNumberOfParameters() == 0) return [];

    // 判断数组类型 数字数组时按顺序绑定参数
    //将 $vars 数组的内部指针指向第一个元素
    reset($vars);
    //$type 变量用于判断 $vars 数组的类型，如果数组的键值从 0 开始，即数字索引数组，则将 $type 设置为 1，否则为 0。
    $type = key($vars) === 0 ? 1 : 0;
    //通过 $reflect->getParameters() 获取参数的反射信息，即参数的 ReflectionParameter 对象数组。
    $params = $reflect->getParameters();

    $args = [];
    //遍历参数数组，处理每个参数的绑定逻辑
    foreach ($params as $param) {
      //获取参数名称
      $name = $param->getName();
      //使用 Str::camelCaseToSnakeCase 方法将其转换为小写下划线命名法的形式
      $lowerName = Str::camelCaseToSnakeCase($name);
      // $param->getType() 获取参数的类型信息，返回一个 ReflectionType 对象
      $reflectionType = $param->getType();
      // 方法参数是可变参数
      if ($param->isVariadic()) {
        //检查参数是否为可变参数（variadic）。如果是，则将已绑定的参数与剩余的参数合并，并返回最终的参数数组
        return array_merge($args, array_values($vars));
      } elseif ($reflectionType instanceof ReflectionNamedType && $reflectionType->isBuiltin(
        ) === false) {
        //否则检查参数的类型信息。如果参数类型为自定义类或接口（即非内置类型）
        //则调用 getObjectParam 方法获取相应的对象参数 $reflectionType->getName()获取对象限定名称
        $args[] = $this->getObjectParam($reflectionType->getName(), $vars);
      } elseif (1 == $type && !empty($vars)) {
        //如果类型判断为数字索引数组（即参数没有指定名称）
        //且 $vars 数组不为空，则从 $vars 数组中弹出第一个值
        //并将其添加到参数数组 $args 中
        $args[] = array_shift($vars);
      } elseif (0 == $type && array_key_exists($name, $vars)) {
        //如果类型判断为关联数组，
        //并且在 $vars 数组中存在与参数名称 $name 相对应的键
        //则将对应的值添加到参数数组 $args 中
        $args[] = $vars[$name];
      } elseif (0 == $type && array_key_exists($lowerName, $vars)) {
        //如果类型判断为关联数组，
        //并且在 $vars 数组中存在与参数名称的小写形式 $lowerName 相对应的键，
        //则将对应的值添加到参数数组 $args 中
        $args[] = $vars[$lowerName];
      } elseif ($param->isDefaultValueAvailable()) {
        //如果参数具有默认值，则将其默认值添加到参数数组 $args 中
        $args[] = $param->getDefaultValue();
      } else {
        $file = $reflect->getFileName();
        $line = $reflect->getStartLine();
        //如果以上条件均不满足，则抛出 ReflectionException 异常，表示缺少必需的参数
        throw new InvalidArgumentException(
          "容器执行反射方法绑定参数时缺少必须参数:$$name,方法所在处 $file:$line"
        );
      }
    }
    return $args;
  }

  /**
   * 获取对象类型的参数值
   * @access protected
   * @param string $className 类名
   * @param array $vars 参数
   * @return object
   * @throws ReflectionException
   */
  protected function getObjectParam(string $className, array &$vars): object
  {
    // 创建 vars 数组的副本
    $array = $vars;

    // 从副本数组中弹出第一个值
    $value = array_shift($array);

    // 检查弹出的值是否是指定类的实例
    if ($value instanceof $className) {
      // 如果是，则将其作为结果
      $result = $value;
      // 从原始 vars 数组中也移除该值
      array_shift($vars);
    } else {
      // 如果不是，则使用容器的 make 方法创建一个新的对象
      $result = $this->make($className);
    }

    // 返回获取的对象参数
    return $result;
  }

  /**
   * 创建类的实例 已经存在则直接获取
   * @param string $abstract 类名或者标识
   * @param array $vars 变量
   * @param bool $newInstance 是否每次都需要创建新实例
   * @return mixed
   * @throws ReflectionException
   */
  public function make(string $abstract, array $vars = [], bool $newInstance = true): mixed
  {
    $isBind = isset($this->binds[$abstract]);
    //解析别名获得完全限定名称
    $abstract = $this->getAlias($abstract);
    //如果已经创建过实例 直接返回
    if (isset($this->instances[$abstract])) {
      return $this->instances[$abstract];
    }
    //如果绑定了别名 和绑定的是一个闭包
    if (isset($this->binds[$abstract]) && $this->binds[$abstract] instanceof Closure) {
      $object = $this->invokeFunction($this->binds[$abstract], $vars);
    } else {
      //反射创建一个类
      $object = $this->invokeClass($abstract, $vars);
    }
    //判断是否需要绑定进容器持久化
    if ($isBind || !$newInstance) {
      $this->bindInstance($abstract, $object);
    }
    return $object;
  }

  /**
   * 根据别名获取真实类名
   * @param string $abstract
   * @return string
   */
  protected function getAlias(string $abstract): string
  {
    if (isset($this->binds[$abstract])) {
      $bind = $this->binds[$abstract];
      if (is_string($bind)) {
        return $this->getAlias($bind);
      }
    }
    return $abstract;
  }

  /**
   * 执行函数或者闭包方法 支持参数调用
   * @access public
   * @param Closure|string $function 函数或者闭包
   * @param array $vars 参数
   * @return mixed
   * @throws ReflectionException
   */
  public function invokeFunction(Closure|string $function, array $vars = []): mixed
  {
    try {
      $reflect = new ReflectionFunction($function);
    } catch (ReflectionException $e) {
      throw new FuncNotFoundException("function not exists: $function()", $function, $e);
    }

    $args = $this->bindParams($reflect, $vars);

    return $function(...$args);
  }

  /**
   * 调用反射执行类的实例化 支持依赖注入
   * @param string $class 类完全限定名
   * @param array $vars 参数
   * @return object
   * @throws ReflectionException
   */
  protected function invokeClass(string $class, array $vars = []): object
  {
    $reflector = new ReflectionClass($class);
    if ($reflector->hasMethod('__make')) {
      $method = $reflector->getMethod('__make');
      if ($method->isPublic() && $method->isStatic()) {
        $args = $this->bindParams($method, $vars);
        return $method->invokeArgs(null, $args);
      }
    }
    $constructor = $reflector->getConstructor();

    $args = $constructor ? $this->bindParams($constructor, $vars) : [];

    return $reflector->newInstanceArgs($args);
  }

  /**
   * 绑定一个类实例到容器
   *
   * @access public
   * @param string $abstract 类完全限定名称
   * @param object $instance 类的实例
   * @return void
   */
  public function bindInstance(string $abstract, object $instance): void
  {
    $abstract = $this->getAlias($abstract);
    $this->instances[$abstract] = $instance;
  }

  /**
   * 调用反射执行方法 支持参数绑定
   * @access public
   * @param mixed $callable
   * @param array $vars 参数
   * @return mixed
   * @throws ReflectionException
   */
  public function invoke(mixed $callable, array $vars = []): mixed
  {
    if ($callable instanceof Closure) {

      return $this->invokeFunction($callable, $vars);

    } elseif (is_string($callable) && !str_contains($callable, '::')) {
      return $this->invokeFunction($callable, $vars);
    } else {
      return $this->invokeMethod($callable, $vars);
    }
  }

  /**
   * 调用反射执行类的方法 支持参数绑定
   * @access public
   * @param mixed $method 方法 [class=>method]|class::method
   * @param array $vars 参数
   * @return mixed
   * @throws ReflectionException
   */
  public function invokeMethod(mixed $method, array $vars = []): mixed
  {
    if (is_array($method)) {
      [$class, $method] = $method;

      $class = is_object($class) ? $class : $this->invokeClass($class);
    } else {
      // 静态方法
      [$class, $method] = explode('::', $method);
    }

    try {
      $reflect = new ReflectionMethod($class, $method);
    } catch (ReflectionException $e) {
      $class = is_object($class) ? get_class($class) : $class;
      throw new FuncNotFoundException(
        'method not exists: ' . $class . '::' . $method . '()', "$class::$method", $e
      );
    }

    $args = $this->bindParams($reflect, $vars);

    try {
      return $reflect->invokeArgs(is_object($class) ? $class : null, $args);
    } catch (ReflectionException $e) {
      throw new FuncNotFoundException(
        'method cannot be executed: ' . $class . '::' . $method . '()', "$class::$method", $e
      );
    }
  }

  public function __isset($name): bool
  {
    return $this->exists($name);
  }

  /**
   * 判断容器中是否存在对象实例
   * @access public
   * @param string $abstract 类名或者标识
   * @return bool
   */
  public function exists(string $abstract): bool
  {
    $abstract = $this->getAlias($abstract);

    return isset($this->instances[$abstract]);
  }

  /**
   * @param $name
   * @return mixed
   * @throws ReflectionException
   */
  public function __get($name)
  {
    return $this->get($name);
  }

  public function __set($name, $value)
  {
    $this->bind($name, $value);
  }

  /**
   * 获取容器中的对象实例
   *
   * @param string $id 标识或类完全限定名称
   * @return object
   * @throws ReflectionException
   */
  public function get(string $id): object
  {
    //判断是否已经绑定
    if ($this->has($id)) {
      return $this->make($id);
    }
    throw new ContainerException('Not found in container: ' . $id);
  }

  /**
   * 判断容器中是否存在类及标识
   *
   * @access public
   * @param string $id 类名或者标识
   * @return bool
   */
  public function has(string $id): bool
  {
    return isset($this->binds[$id]) || isset($this->instances[$id]);
  }

  /**
   * 绑定一个类、闭包、实例、接口实现到容器
   *
   * @access public
   * @param array|string $abstract 标识或接口
   * @param object|string|null $concrete 类完全限定名称、闭包或类实例
   * @return void
   */
  public function bind(array|string $abstract, object|string|null $concrete = null): void
  {
    //如果为数组则 则是绑定多个类实现
    if (is_array($abstract)) {
      foreach ($abstract as $key => $val) {
        $this->bind($key, $val);
      }
    } elseif ($concrete instanceof Closure) {
      //绑定闭包
      $this->binds[$abstract] = $concrete;
    } elseif (is_object($concrete)) {
      //往容器中新增一个实例
      $classname = get_class($concrete);
      $this->binds[$abstract] = $classname;
      $this->bindInstance($classname, $concrete);
    } else {
      $abstract = $this->getAlias($abstract);
      if ($abstract != $concrete) {
        $this->binds[$abstract] = $concrete;
      }
    }
  }

  public function __unset($name)
  {
    $this->remove($name);
  }

  /**
   * 删除容器中的对象实例
   * @access public
   * @param string $name 类名或者标识
   * @return void
   */
  public function remove(string $name): void
  {
    $name = $this->getAlias($name);

    if (isset($this->instances[$name])) {
      unset($this->instances[$name]);
    }
  }

  #[ReturnTypeWillChange]
  public function offsetExists($offset): bool
  {
    return $this->exists($offset);
  }

  /**
   * @param $offset
   * @return object
   * @throws ReflectionException
   */
  #[ReturnTypeWillChange]
  public function offsetGet($offset): object
  {
    return $this->make($offset);
  }

  #[ReturnTypeWillChange]
  public function offsetSet($offset, $value): void
  {
    $this->bind($offset, $value);
  }

  #[ReturnTypeWillChange]
  public function offsetUnset($offset): void
  {
    $this->remove($offset);
  }

  public function count(): int
  {
    return count($this->instances);
  }

  public function getIterator(): ArrayIterator
  {
    return new ArrayIterator($this->instances);
  }
}
