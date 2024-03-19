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
use Override;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ViSwoole\Core\Exception\ClassNotFoundException;
use ViSwoole\Core\Exception\ContainerException;
use ViSwoole\Core\Exception\FuncNotFoundException;
use ViSwoole\Core\Exception\MethodNotFoundException;

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
   * 容器绑定标识
   * @var array
   */
  protected array $bindings = [];
  /**
   * 容器中缓存的单实例
   * @var array
   */
  private array $singleInstance = [];

  /**
   * 获取单实例
   *
   * @return static
   */
  public static function sign(): static
  {
    return self::create();
  }

  /**
   * 获取当前容器的实例（单例）
   * @access public
   * @return static
   */
  public static function create(): static
  {
    if (!isset(static::$instance)) {
      static::$instance = new static;
    }
    return static::$instance;
  }

  /**
   * 设置当前容器的实例
   *
   * @access public
   * @param Container $instance
   * @return void
   */
  protected static function setInstance(Container $instance): void
  {
    static::$instance = $instance;
  }

  /**
   * 执行闭包函数，支持依赖参数注入。
   *
   * @access public
   * @param string|Closure $function
   * @param array $vars
   * @return mixed
   */
  public function invokeFunction(string|Closure $function, array $vars = []): mixed
  {
    try {
      $reflect = new ReflectionFunction($function);
    } catch (ReflectionException $e) {
      throw new FuncNotFoundException("函数不存在$function()", $function, $e);
    }
    $args = $this->bindParams($reflect, $vars);
    return $reflect->invoke(...$args);
  }

  /**
   * 注入参数
   *
   * @param ReflectionFunctionAbstract $reflect 反射实例
   * @param array $vars 传递的参数数组
   * @return array
   */
  private function bindParams(ReflectionFunctionAbstract $reflect, array $vars = []): array
  {
    // 获取参数列表
    $params = $reflect->getParameters();
    $isIndexArray = key($vars) === 0;
    // 如果方法或函数接收的是一个可变参数，则直接返回参数变量列表
    if (!empty($params) && $params[0]->isVariadic()) {
      return $vars;
    }
    $args = [];
    foreach ($params as $index => $param) {
      $paramName = $param->getName();
      $paramType = $param->getType();
      // 根据数组类型决定使用索引或参数名
      $key = $isIndexArray ? $index : $paramName;
      // 判断参数接收的类型是否为内置类型
      if (!$paramType || $paramType->isBuiltin()) {
        // 检查是否传入了该下标或参数名
        if (array_key_exists($key, $vars)) {
          $value = $vars[$key];
        } else {
          // 如果参数有默认值，则使用默认值，否则抛出异常
          if ($param->isDefaultValueAvailable()) {
            $value = $param->getDefaultValue();
          } else {
            $funcName = $reflect->getName();
            throw new InvalidArgumentException(
              "在执行容器反射调用{$funcName}时未传递其必填参数{$paramName}"
            );
          }
        }
      } else {
        // 如果传入了参数名，则使用传入的值；否则，创建实例
        $value = array_key_exists($key, $vars)
          ? $vars[$key]
          : $this->make($paramType->getName());
      }
      $args[$key] = $value;
    }
    return $args;
  }

  /**
   * 创建依赖实例，已经存在则直接获取。
   *
   * @param string $abstract 类名或标识
   * @param array $vars 参数
   * @param bool $autoBind 是否自动绑定实例（绑定过后无需每次都创建新的实例，仅对类有效）
   * @return mixed 返回闭包函数运行结果，或类实例对象
   */
  public function make(string $abstract, array $vars = [], bool $autoBind = false): mixed
  {
    $concrete = $this->getTheRealConcrete($abstract);
    // 如果已经缓存过实例 直接返回
    if (is_string($concrete) && isset($this->singleInstance[$concrete])) {
      return $this->singleInstance[$concrete];
    }
    $result = $this->{($concrete instanceof Closure) ? 'invokeFunction' : 'invokeClass'}(
      $concrete, $vars
    );
    // 如果需要缓存实例，则将实例缓存起来
    if ($autoBind && is_string($concrete)) {
      $this->singleInstance[$concrete] = $result;
      // 绑定进服务
      if (!isset($this->bindings[$abstract])) {
        $this->bindings[$abstract] = $concrete;
        $this->singleInstance[$concrete] = $result;
      }
    }
    return $result;
  }

  /**
   * 通过标识获取到真实映射的类名
   *
   * @param string $abstract 标识
   * @return string|Closure 获取真实的类名或函数
   */
  private function getTheRealConcrete(string $abstract): string|Closure
  {
    if (isset($this->bindings[$abstract])) {
      $bind = $this->bindings[$abstract];
      // 如果是闭包则直接返回闭包
      if ($bind instanceof Closure) return $bind;
      // 判断是否为字符串，为字符串则继续递归判断
      if (is_string($bind)) return $this->getTheRealConcrete($bind);
    }
    return $abstract;
  }

  /**
   * 调用反射执行函数、匿名函数、以及类或方法，支持依赖注入。
   *
   * @access public
   * @param callable|string $callable 接收[$object|className,$method]或函数,匿名函数，以及类名或函数名
   * @param array $vars 参数
   * @return mixed
   */
  public function invoke(callable|string $callable, array $vars = []): mixed
  {
    if ($callable instanceof Closure) {
      return $this->invokeFunction($callable, $vars);
    }

    if (str_contains($callable, '::') || is_array($callable)) {
      return $this->invokeMethod($callable, $vars);
    }

    if (class_exists($callable)) {
      return $this->invokeClass($callable, $vars);
    }

    if (function_exists($callable)) {
      return $this->invokeFunction($callable, $vars);
    }
    // 如果找不到对应的函数或类，抛出异常
    throw new FuncNotFoundException("{$callable}函数、类或方法未找到", $callable);
  }

  /**
   * 调用反射执行类的方法，支持依赖注入。
   * @access public
   * @param array|string $method 方法[class,method]|class::method
   * @param array $vars 参数
   * @return mixed
   */
  public function invokeMethod(array|string $method, array $vars = []): mixed
  {
    // 解析方法
    [$class, $method] = is_array($method) ? $method : explode('::', $method);
    // 创建实例
    $instance = is_object($class) ? $class : $this->invokeClass($class);
    try {
      // 获取方法的反射信息
      $reflect = new ReflectionMethod($instance, $method);
    } catch (ReflectionException $e) {
      $class = is_object($class) ? get_class($class) : $class;
      throw new MethodNotFoundException(
        "在{$class}类中未找到{$method}方法，或类不存在：{$e->getMessage()}",
        "$class::$method",
        $e
      );
    }
    // 绑定参数
    $args = $this->bindParams($reflect, $vars);
    try {
      // 调用方法并传入参数
      return $reflect->invokeArgs(is_object($instance) ? $instance : null, $args);
    } catch (ReflectionException $e) {
      $class = is_object($class) ? get_class($class) : $class;
      throw new MethodNotFoundException(
        "在{$class}类中未找到{$method}方法：{$e->getMessage()}",
        "$class::$method",
        $e
      );
    }
  }

  /**
   * 调用反射执行类的实例化，支持依赖注入。
   *
   * @access public
   * @param string $class
   * @param array $vars
   * @return object
   * @throws ContainerException
   */
  public function invokeClass(string $class, array $vars = []): object
  {
    if (!class_exists($class)) throw new ClassNotFoundException(
      "需要反射执行的类{$class}不存在", $class
    );
    $reflector = new ReflectionClass($class);
    // 判断是否存在自定义__make方法
    if ($reflector->hasMethod('__make')) {
      $method = $reflector->getMethod('__make');
      // 如果存在__make方法，且该方法为公开的静态方法则执行该方法
      if ($method->isPublic() && $method->isStatic()) {
        $args = $this->bindParams($method, $vars);
        try {
          return $method->invokeArgs(...$args);
        } catch (ReflectionException $e) {
          throw new ContainerException(
            "反射执行$class::__make方法失败,{$e->getMessage()}", $e->getCode(), $e
          );
        }
      }
    }
    $constructor = $reflector->getConstructor();

    $args = $constructor ? $this->bindParams($constructor, $vars) : [];

    try {
      $instance = $reflector->newInstanceArgs($args);
    } catch (ReflectionException $e) {
      throw new ContainerException(
        "反射执行{$class}构造方法失败,{$e->getMessage()}", $e->getCode(), $e
      );
    }
    if (is_null($instance)) {
      throw new ContainerException(
        "反射执行{$class}构造方法失败，调用ReflectionClass::newInstanceArgs()方法返回NULL"
      );
    }
    return $instance;
  }

  /**
   * 获取容器中的对象实例
   *
   * @param string $id 标识或类完全限定名称
   * @return object
   */
  #[Override] public function get(string $id): object
  {
    // 判断是否已经绑定
    return $this->make($id);
  }

  /**
   * 判断是否存在对象实例
   *
   * @param string $id
   * @return bool
   */
  #[Override] public function has(string $id): bool
  {
    return isset($this->bindings[$id]);
  }

  #[Override] public function getIterator(): ArrayIterator
  {
    return new ArrayIterator($this->singleInstance);
  }

  #[Override] public function offsetExists(mixed $offset): bool
  {
    return $this->exists($offset);
  }

  /**
   * 判断容器中是否存在对象实例
   *
   * @access public
   * @param string $abstract 类名或者标识
   * @return bool
   */
  public function exists(string $abstract): bool
  {
    $concreteName = $this->getTheRealConcrete($abstract);
    return isset($this->singleInstance[$concreteName]);
  }

  #[Override] public function offsetGet(mixed $offset): mixed
  {
    return $this->make($offset);
  }

  #[Override] public function offsetSet(mixed $offset, mixed $value): void
  {
    $this->bind($offset, $value);
  }

  /**
   * 注册服务到容器中,支持批量注册。
   *
   * @param string|array $abstract 服务标识或接口名称
   * @param mixed|Countable|string $concrete 服务的具体实现类、闭包函数、对象实例、其他服务标识
   */
  public function bind(string|array $abstract, mixed $concrete): void
  {
    if (is_array($abstract)) {
      foreach ($abstract as $key => $val) {
        $this->bind($key, $val);
      }
    } elseif ($concrete instanceof Closure) {
      $this->bindings[$abstract] = $concrete;
    } elseif (is_object($concrete)) {
      // 如果传入的$concrete是对象实例，则将其缓存，并映射其实例名称
      $className = get_class($concrete);
      // 绑定映射
      $this->bindings[$abstract] = $className;
      // 缓存实例
      $this->singleInstance[$className] = $concrete;
    } elseif (is_string($concrete)) {
      // 如果为无效类名同时未绑定到容器中，则抛出异常
      if (!class_exists($concrete) && !isset($this->bindings[$concrete])) {
        throw new InvalidArgumentException(
          "Container::bind方法参数2错误:给定的字符串非有效类名，且未绑定到容器中"
        );
      }
      // 绑定到容器
      $this->bindings[$abstract] = $concrete;
    } else {
      throw new InvalidArgumentException(
        "Container::bind方法参数2错误:绑定到容器的内容必须是可调用的闭包函数、有效的类名、其他已绑定服务标识。"
      );
    }
  }

  #[Override] public function offsetUnset(mixed $offset): void
  {
    $this->remove($offset);
  }

  /**
   * 删除容器中的对象实例
   * @access public
   * @param string $abstract
   * @return void
   */
  public function remove(string $abstract): void
  {
    $abstract = $this->getTheRealConcrete($abstract);

    if (isset($this->singleInstance[$abstract])) unset($this->singleInstance[$abstract]);
  }

  public function __unset($name)
  {
    $this->remove($name);
  }

  /**
   * 获取容器中绑定实例的数量
   *
   * @return int
   */
  #[Override] public function count(): int
  {
    return count($this->bindings);
  }
}
