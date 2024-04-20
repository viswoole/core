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
use IteratorAggregate;
use Override;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionNamedType;
use ViSwoole\Core\Common\Arr;
use ViSwoole\Core\Exception\ClassNotFoundException;
use ViSwoole\Core\Exception\ContainerException;
use ViSwoole\Core\Exception\FuncNotFoundException;
use ViSwoole\Core\Exception\MethodNotFoundException;
use ViSwoole\Core\Exception\ServiceNotFoundException;

/**
 * 容器与依赖注入类
 */
class Container implements ContainerInterface, ArrayAccess, IteratorAggregate, Countable
{
  /**
   * 容器对象实例
   * @var ContainerInterface
   */
  protected static ContainerInterface $instance;
  /**
   * 服务绑定标识
   * @var array
   */
  protected array $bindings = [];
  /**
   * @var array 解析类时的需要触发的回调
   */
  protected array $invokeCallback = [];
  /**
   * 容器中缓存的单实例
   * @var array
   */
  protected array $singleInstance = [];
  /**
   * @var string[] 定义需要排除的类/接口，每次通过容器反射执行该类时都会重新实例化
   */
  protected array $exclude = [];

  protected function __construct()
  {
    // 绑定容器短命名标识
    $this->bind('container', $this);
    // 将容器类名映射到标识
    $this->bind(__CLASS__, 'container');
    // 容器接口标识绑定
    $this->bind(ContainerInterface::class, 'container');
    // 工厂单例赋值
    self::$instance = $this;
  }

  /**
   * 绑定服务到容器中
   *
   * @param string $abstract 服务标识或接口名称
   * @param mixed|Countable|string $concrete 服务的具体实现类、闭包函数、对象实例、其他服务标识
   */
  public function bind(string $abstract, mixed $concrete): void
  {
    if (isset($this->bindings[$abstract])) {
      trigger_error('容器中存在相同服务标识覆盖: ' . $abstract . '，请检查', E_USER_WARNING);
    }
    if ($concrete instanceof Closure) {
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
      if (!class_exists($concrete) && !$this->has($concrete)) {
        throw new ContainerException(
          'Container::bind方法参数2($concrete)错误:给定的字符串非有效类名，且未绑定到容器中'
        );
      }
      // 绑定到容器
      $this->bindings[$abstract] = $concrete;
    } else {
      throw new ContainerException(
        'Container::bind方法参数2($concrete)错误:绑定到容器的内容必须是可调用的闭包函数|有效的类名|类实例|其他已绑定服务标识。'
      );
    }
  }

  /**
   * 通过标识、接口、类名判断是否已注册服务
   *
   * @param string $id
   * @return bool
   */
  #[Override] public function has(string $id): bool
  {
    // 通过标识获取到真实映射的类名
    $concrete = $this->getTheRealConcrete($id);
    if ($concrete !== $id) return true;
    return isset($this->bindings[$id]) || isset($this->singleInstance[$id]);
  }

  /**
   * 通过标识获取到真实映射的类名
   *
   * @param string $abstract 标识
   * @return string|Closure 获取真实的类名或函数
   */
  protected function getTheRealConcrete(string $abstract): string|Closure
  {
    if (isset($this->bindings[$abstract])) {
      $bind = $this->bindings[$abstract];
      // 如果是闭包则直接返回闭包
      if ($bind instanceof Closure) return $bind;
      // 判断是否为字符串，为字符串则继续递归判断
      if (is_string($bind)) {
        // 避免死循环
        if ($bind === $abstract) return $bind;
        return $this->getTheRealConcrete($bind);
      }
    }
    return $abstract;
  }

  /**
   * 获取容器工厂单实例
   *
   * @return ContainerInterface|static
   */
  public static function factory(): ContainerInterface|static
  {
    if (!isset(self::$instance)) new static();
    return self::$instance;
  }

  /**
   * 添加一个需要排除的类或接口名称
   *
   * @access public
   * @param string $class
   * @return void
   */
  public function addExclude(string $class): void
  {
    $this->exclude[] = $class;
  }

  /**
   * 移除一个需要排除的类或接口名称
   *
   * @access public
   * @param string $class
   * @return void
   */
  public function delExclude(string $class): void
  {
    unset($this->exclude[$class]);
  }

  /**
   * 批量绑定服务到容器中
   *
   * @access public
   * @param array $binds
   */
  public function binds(array $binds): void
  {
    foreach ($binds as $key => $val) {
      $this->bind($key, $val);
    }
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
      throw new FuncNotFoundException("函数不存在$function()", $e);
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
    // 如果没有参数 则返回空待注入参数数组
    if (empty($params)) return [];
    // 判断是否为索引数组
    $isIndexArray = array_values($vars) === $vars;
    $args = [];
    foreach ($params as $index => $param) {
      // 如果是可变参数则返回参数数组
      if ($param->isVariadic()) return array_merge($args, $vars);
      /** 参数类型 */
      $paramType = $param->getType();
      // 参数名称
      $name = $param->getName();
      // 键
      $key = $isIndexArray ? $index : $name;
      // 参数默认值
      $default = $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null;
      if (is_null($paramType)) {
        $value = Arr::arrayPopValue($vars, $key, $default);
      } elseif ($paramType instanceof ReflectionNamedType) {
        $value = $this->bindValue($vars, $paramType, $key, $default);
      } else {
        // 联合类型直接获取
        $value = Arr::arrayPopValue($vars, $key, $default);
      }
      $args[$key] = $value;
    }
    return $args;
  }

  /**
   * 绑定依赖注入的值
   *
   * @param array $vars 传递的参数数组
   * @param ReflectionNamedType $paramType 参数类型
   * @param string|int $key 参数名称
   * @param mixed|null $default 默认值
   * @return mixed
   */
  private function bindValue(
    array               &$vars,
    ReflectionNamedType $paramType,
    string|int          $key,
    mixed               $default
  ): mixed
  {
    $value = Arr::arrayPopValue($vars, $key, $default);
    if (!$paramType->isBuiltin()) {
      $class = $paramType->getName();
      // 判断是否直接传入了需要注入的类实例
      if ($value instanceof $class) return $value;
      if ($this->has($class)) {
        // 获取依赖
        $value = $this->make($class, is_array($value) ? $value : [$value]);
      } else {
        // 实例化一个类
        $value = $this->invokeClass($class, is_array($value) ? $value : [$value]);
      }
    }
    return $value;
  }

  /**
   * 获取容器中的服务，已经存在则直接获取。
   *
   * @param string $abstract 类名或标识
   * @param array $vars 参数
   * @return mixed 返回闭包函数运行结果，或类实例对象
   */
  public function make(string $abstract, array $vars = []): mixed
  {
    if (!$this->has($abstract)) throw new ServiceNotFoundException("未找到{$abstract}服务");
    $concrete = $this->getTheRealConcrete($abstract);
    $key = is_string($concrete) ? $concrete : $abstract;
    // 如果已经缓存过实例 直接返回
    if (isset($this->singleInstance[$key])) {
      return $this->singleInstance[$concrete];
    }
    $result = $this->{$concrete instanceof Closure ? 'invokeFunction' : 'invokeClass'}(
      $concrete,
      $vars
    );
    // 判断是否需要缓存单实例
    if (is_object($result) && !$this->isExclude($result)) $this->singleInstance[$key] = $result;
    return $result;
  }

  /**
   * 通过类名或接口、类实例判断是否已排除缓存为单例
   *
   * @param string|object $instance
   * @return bool
   */
  public function isExclude(string|object $instance): bool
  {
    if (is_string($instance)) return in_array($instance, $this->exclude);
    if (in_array(get_class($instance), $this->exclude)) return true;
    // 遍历匹配整个列表，判断是否需要排除
    foreach ($this->exclude as $exclude) if ($instance instanceof $exclude) return true;
    return false;
  }

  /**
   * 调用反射执行类的实例化，支持依赖注入。
   *
   * @access public
   * @param string $class
   * @param array $vars
   * @return mixed
   * @throws ContainerException
   */
  public function invokeClass(string $class, array $vars = []): mixed
  {
    if (!class_exists($class)) throw new ClassNotFoundException(
      "需要反射执行的类{$class}不存在"
    );
    $reflector = new ReflectionClass($class);
    // 判断是否存在自定义__make方法
    if ($reflector->hasMethod('__make')) {
      $method = $reflector->getMethod('__make');
      // 如果存在__make方法，且该方法为公开的静态方法则执行该方法
      if ($method->isPublic() && $method->isStatic()) {
        $args = $this->bindParams($method, $vars);
        try {
          $instance = $method->invokeArgs(null, $args);
          if (!($instance instanceof $class)) {
            throw new ContainerException(
              "$class::__make方法返回的实例必须是{$class}类的实例"
            );
          }
          // 触发回调
          $this->invokeAfter($class, $instance);
          return $instance;
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
        "反射调用{$class}构造方法失败，{$e->getMessage()}", $e->getCode(), $e
      );
    }
    if (is_null($instance)) {
      throw new ContainerException(
        "反射调用{$class}构造方法失败，调用ReflectionClass::newInstanceArgs()方法返回NULL"
      );
    }
    $this->invokeAfter($class, $instance);
    return $instance;
  }

  /**
   * 执行invokeClass回调
   *
   * @access protected
   * @param string $class 对象类名
   * @param object $object 容器对象实例
   * @return void
   */
  protected function invokeAfter(string $class, object $object): void
  {
    if (isset($this->invokeCallback['*'])) {
      foreach ($this->invokeCallback['*'] as $callback) {
        $callback($object, $this);
      }
    }
    if (isset($this->invokeCallback[$class])) {
      foreach ($this->invokeCallback[$class] as $callback) {
        $callback($object, $this);
      }
    }
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
    } elseif (is_array($callable)) {
      return $this->invokeMethod($callable, $vars);
    } elseif (is_string($callable)) {
      if (str_contains($callable, '::')) {
        return $this->invokeMethod($callable, $vars);
      } elseif (class_exists($callable)) {
        return $this->invokeClass($callable, $vars);
      } elseif (function_exists($callable)) {
        return $this->invokeFunction($callable, $vars);
      }
    }
    // 如果找不到对应的函数或类，抛出异常
    throw new FuncNotFoundException("{$callable}函数或类未找到");
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
    try {
      if (is_array($method)) {
        // 创建实例
        $instance = is_object($method[0]) ? $method[0] : $this->invokeClass($method[0]);
        $reflect = new ReflectionMethod($instance, $method[1]);
      } else {
        $instance = null;
        $reflect = new ReflectionMethod($method);
      }
      // 绑定参数
      $args = $this->bindParams($reflect, $vars);
      // 调用方法并传入参数
      return $reflect->invokeArgs($instance, $args);
    } catch (ReflectionException $e) {
      throw new MethodNotFoundException(
        $e->getMessage(),
        $e
      );
    }
  }

  /**
   * 注册一个解析事件回调
   *
   * @access public
   * @param string|Closure $abstract 事件标识或回调闭包
   * @param Closure|null $callback 事件回调
   * @return void
   */
  public function resolving(string|Closure $abstract, Closure $callback = null): void
  {
    if (is_string($abstract)) {
      $key = $this->getTheRealConcrete($abstract);
    } else {
      $key = '*';
      $callback = $abstract;
    }
    $this->invokeCallback[$key][] = $callback;
  }

  /**
   * 删除解析事件回调
   *
   * @access public
   * @param string|Closure $abstract 标识或回调闭包
   * @param Closure|null $callback
   * @return void
   */
  public function removeCallback(string|Closure $abstract, Closure $callback = null): void
  {
    if (is_string($abstract)) {
      $key = $this->getTheRealConcrete($abstract);
    } else {
      $key = '*';
      $callback = $abstract;
    }
    if (isset($this->invokeCallback[$key])) {
      $index = array_search($callback, $this->invokeCallback[$key]);
      if ($index !== false) {
        unset($this->invokeCallback[$key][$index]);
      }
    }
  }

  /**
   * 获取容器中的对象实例
   *
   * @param string $id 标识或类完全限定名称
   * @return mixed
   */
  #[Override] public function get(string $id): mixed
  {
    return $this->make($id);
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
   * 判断容器中是否注册单实例
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

  #[Override] public function offsetUnset(mixed $offset): void
  {
    $this->remove($offset);
  }

  /**
   * 删除容器中的服务实例
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
   * 获取容器中的服务实例
   *
   * @param string $name
   * @return mixed
   */
  public function __get(string $name)
  {
    return $this->make($name);
  }

  /**
   * 绑定服务到容器
   *
   * @param string $name
   * @param $value
   * @return void
   */
  public function __set(string $name, $value): void
  {
    $this->bind($name, $value);
  }

  /**
   * 获取容器中实例的数量
   *
   * @return int
   */
  #[Override] public function count(): int
  {
    return count($this->singleInstance);
  }
}
