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

namespace ViSwoole\Core\Validate;

use Closure;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use RuntimeException;
use ViSwoole\Core\App;
use ViSwoole\Core\Exception\ValidateException;
use ViSwoole\Core\Validate\Contract\RuleInterface;

/**
 * 类型解析工具
 */
final class ShapeTool
{
  /**
   * @var array 缓存解析好的结构,减少解析时间
   */
  private static array $cacheShape = [];

  /**
   * 获取类指定属性的类型
   *
   * @access public
   * @param object|string $objectOrClass 类实例或类名称
   * @param string $property_name 要反射的属性名称
   * @param bool $cache 缓存结果，默认TRUE
   * @return array{name:string,type:string,rules:array<string,array>,required:bool,default:mixed,desc:string,depend:bool}
   */
  public static function getPropertyShape(
    object|string $objectOrClass,
    string        $property_name,
    bool          $cache = true
  ): array
  {
    if ($cache) {
      $onlyKey = __METHOD__
        . '_' . (is_object($objectOrClass) ? get_class(
          $objectOrClass
        ) : $objectOrClass) . '_' . $property_name;
      $shape = self::getCache($onlyKey);
      if ($shape) return $shape;
    }
    try {
      $Reflection = new ReflectionProperty($objectOrClass, $property_name);
    } catch (ReflectionException $e) {
      throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
    }
    $shape = self::parseTypeShape($Reflection->getDocComment(), $Reflection);
    if ($cache) self::setCache($onlyKey, $shape);
    return $shape;
  }

  /**
   * 获取缓存
   *
   * @param string $key
   * @return array|null
   */
  private static function getCache(string $key): ?array
  {
    $key = md5($key);
    if (array_key_exists($key, self::$cacheShape)) return self::$cacheShape[$key];
    return null;
  }

  /**
   * 解析类型结构
   *
   * @return array{name:string,type:string,rules:array<string,array>,required:bool,default:mixed,desc:string,depend:bool}
   */
  private static function parseTypeShape(
    string|false                           $doc,
    ReflectionProperty|ReflectionParameter $reflector
  ): array
  {
    /**类型*/
    $type = (string)($reflector->getType() ?? 'mixed');
    /**是否为app依赖*/
    $depend = App::factory()->has($type);
    // 原生注解
    $Attributes = $reflector->getAttributes();
    // 额外的验证规则
    $rules = [];
    foreach ($Attributes as $Attribute) {
      $implements = class_implements($Attribute->getName());
      if (in_array(RuleInterface::class, $implements)) {
        $rules[$Attribute->getName()] = $Attribute->getArguments();
      }
    }
    if (str_starts_with($type, '?')) $type = substr($type, 1) . '|null';
    /**描述*/
    $desc = '';
    // 提取属性注释描述部分
    if ($reflector instanceof ReflectionProperty) {
      if (is_string($doc)) $desc = self::extractPropertyTypeAnnotation($doc);
      $default = $reflector->getDefaultValue();
      // 如果没有设置默认值且没有标记null类型
      $required = !$reflector->hasDefaultValue() && !str_contains($type, 'null');
    } else {
      if (is_string($doc)) $desc = self::extractParamTypeAnnotation($doc, $reflector->getName());
      $default = $reflector->isDefaultValueAvailable() ? $reflector->getDefaultValue() : null;
      $required = !$reflector->allowsNull();
    }
    /**名称*/
    $name = $reflector->getName();
    return compact('name', 'type', 'rules', 'required', 'default', 'desc', 'depend');
  }

  /**
   * 从注释文档中提取到属性说明
   *
   * @param string $doc
   * @return string
   */
  private static function extractPropertyTypeAnnotation(string $doc): string
  {
    if (empty($doc)) return $doc;
    if (preg_match(
      '/@var\s+.*?\s+([\s\S]*?)(?=\s*(?:\r\n|\r|\n|\* @))/', $doc,
      $matches
    )) {
      return $matches[1] ?? '';
    }
    return '';
  }

  /**
   * 从注释文档中提取到参数说明
   *
   * @param string $doc 完整的doc注释
   * @param string $param_name 参数名称
   * @return string
   */
  private static function extractParamTypeAnnotation(string $doc, string $param_name): string
  {
    if (empty($doc)) return $doc;
    if (preg_match(
      '/@param\s+\S+\s+\$' . preg_quote(
        $param_name, '/'
      ) . '\s+([\s\S]*?)(?=\s*(?:\r\n|\r|\n|\* @))/', $doc,
      $matches
    )) {
      return $matches[1] ?? '';
    }
    return '';
  }

  /**
   * 缓存
   *
   * @param string $key
   * @param array $shape
   * @return void
   */
  private static function setCache(string $key, array $shape): void
  {
    $key = md5($key);
    if (!array_key_exists($key, self::$cacheShape)) self::$cacheShape[$key] = $shape;
  }

  /**
   * 验证类型结构
   *
   * @access public
   * @param string $key 链路
   * @param array $array 要检测的数组
   * @param array<int|string,array{name:string,type:string,rules:array<string,array>,required:bool,default:mixed,desc:string,depend:bool}> $shapes 类型结构
   * @return array 验证成功返回传入的数组
   */
  public static function validateShape(array $shapes, mixed $array, string $key = ''): array
  {
    // 如果不是传入的数组直接抛出错误
    if (!is_array($array)) throw new ValidateException("{$key}参数必须是关联数组");
    $safetyData = [];
    foreach ($shapes as $shape) {
      // 参数名称
      $name = $shape['name'];
      // 参数默认值
      $default = $shape['default'];
      // 传入的值
      $value = $array[$name] ?? $default;
      // 是否必填
      $required = $shape['required'];
      // 描述
      $selfKey = empty($shape['desc']) ? $name : $shape['desc'] . (app_debug() ? "($name)" : '');
      // 错误链路
      $errKey = empty($key) ? $selfKey : (app_debug() ? "$key.$selfKey" : $selfKey);
      // 类型字符串
      $type = $shape['type'];
      // 是否为系统自动依赖注入
      $depend = $shape['depend'];
      if ($depend) {
        $safetyData[$name] = App::factory()->make($type);
        continue;
      }
      // 判断是否为空
      if (is_null($value)) {
        // 如果是必填参数则直接返回错误
        if ($required) {
          throw new ValidateException($errKey . '不能为空');
        } else {
          $safetyData[$name] = $value;
          continue;
        }
      }
      if ($type === 'mixed') {
        $safetyData[$name] = $value;
        continue;
      }
      if (str_contains($type, '|')) {
        // 验证联合类型
        $value = self::checkTypes($errKey, $value, $type);
      } else {
        // 验证单独的类型
        $value = self::checkType($errKey, $value, $type);
      }
      // 遍历验证 规则
      $rules = $shape['rules'];
      foreach ($rules as $rule => $params) {
        (new $rule(...$params))->validate($errKey, $value);
      }
      $safetyData[$name] = $value;
    }
    return $safetyData;
  }

  /**
   * 检测值是否符合多个类型之一
   *
   * @param string $key
   * @param mixed $value
   * @param string $types
   * @return mixed
   */
  private static function checkTypes(string $key, mixed $value, string $types): mixed
  {
    $valid = false;
    $typesArray = explode('|', $types);
    foreach ($typesArray as $type) {
      try {
        $value = self::checkType($key, $value, $type);
        $valid = true;
      } catch (ValidateException) {
        // 不做处理
      }
    }
    if (!$valid) {
      $valueType = gettype($value);
      throw new ValidateException("{$key}必须为 $types 类型，$valueType 给定");
    }
    return $value;
  }

  /**
   * 检测值是否符合指定的类型
   *
   * @param string $key
   * @param mixed $value
   * @param string $type
   * @return mixed
   */
  private static function checkType(string $key, mixed $value, string $type): mixed
  {
    $valueType = gettype($value);
    if (TypeTool::isAtomicType($type)) {
      // 验证内置类型
      $valid = TypeTool::$type($value);
      if (!$valid) throw new ValidateException("{$key}必须为 $type 类型，$valueType 给定");
    } elseif (str_contains($type, '&')) {
      $valid = TypeTool::intersection($type, $value);
      if (!$valid) throw new ValidateException("{$key}必须为 $type 类型，$valueType 给定");
    } elseif (enum_exists($type)) {
      $value = TypeTool::enum($key, $type, $value);
    } elseif (class_exists($type)) {
      // 对类进行验证
      if (!$value instanceof $type && !interface_exists($type)) {
        $shape = self::getParamTypeShape($type);
        $params = self::validateShape($shape, $value, $key);
        if (is_string(array_key_first($shape))) {
          $instance = new $type();
          foreach ($params as $k => $v) {
            $instance->{$k} = $v;
          }
          $value = $instance;
        } else {
          // 如果验证通过，则将值注入,得到新实例
          $value = App::factory()->invokeClass($type, $value);
        }
      }
    }
    return $value;
  }

  /**
   * 获取函数、方法或类构造函数的参数类型结构
   *
   * 注意：如果给定的类不存在构造方法，则会返回类的public属性列表做为参数，可通过得到的shape数组键值判断是属性还是参数
   *
   * Example usage:
   * ```
   * $shapes = ShapeTool::getParamTypeShape(function (Example $user){});
   * $shapes = ShapeTool::getParamTypeShape([new Example(),'method']);
   * $shapes = ShapeTool::getParamTypeShape([Example::class,'staticMethod']);
   * $shapes = ShapeTool::getParamTypeShape(Example::staticMethod);
   * ```
   *
   * @access public
   * @param Closure|array|string $callable
   * @param bool $cache 缓存结果，默认TRUE
   * @return array<int|string,array{name:string,type:string,rules:array<string,array>,required:bool,default:mixed,desc:string,depend:bool,variadic:bool}>
   */
  public static function getParamTypeShape(
    Closure|array|string $callable,
    bool                 $cache = true
  ): array
  {
    if ($cache) {
      $onlyKey = __METHOD__ . '_';
      if (is_array($callable)) {
        $onlyKey .= (is_object($callable[0]) ? get_class(
            $callable[0]
          ) : $callable[0]) . '::' . $callable[1];
        $shape = self::getCache($onlyKey);
      } elseif (is_string($callable)) {
        $onlyKey .= $callable;
        $shape = self::getCache($onlyKey);
      }
      if (isset($shape)) return $shape;
    }
    try {
      if ($callable instanceof Closure) {
        $reflection = new ReflectionFunction($callable);
      } elseif (is_string($callable)) {
        if (str_contains($callable, '::')) {
          $reflection = new ReflectionMethod($callable);
        } elseif (class_exists($callable)) {
          $reflection = (new ReflectionClass($callable))->getConstructor();
          // 如果没有构造函数 则返回空数组
          if (is_null($reflection)) return self::getClassPropertyShape($callable);
        } elseif (function_exists($callable)) {
          $reflection = new ReflectionFunction($callable);
        }
      } elseif (is_array($callable)) {
        $reflection = new ReflectionMethod($callable[0], $callable[1]);
      }
    } catch (ReflectionException $e) {
      throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
    }
    if (!isset($reflection)) throw new InvalidArgumentException(
      '$callable参数类型必须是Closure|[class|object,method]|class::method|function_name|class_name'
    );
    $params = $reflection->getParameters();
    $doc = $reflection->getDocComment();
    $shapes = [];
    foreach ($params as $param) {
      $shape = self::parseTypeShape($doc, $param);
      $shapes[] = array_merge(
        $shape,
        ['variadic' => $param->isVariadic()]
      );
    }
    if ($cache) self::setCache($onlyKey, $shapes);
    return $shapes;
  }

  /**
   * 获取类的属性结构
   *
   * Example usage:
   * ```
   * class MyClass{
   *   public string $name = 'viswoole';
   * }
   * $shapes = ShapeTool::getPropertyShape(MyClass::class);
   * // $shape如下
   * $shapes = ['name'=>['name'=>'name','type'=>'string','rules'=>[],'required'=>false,'default'=>'viswoole','desc'=>'没有属性var描述将会为空']];
   * ```
   * @access public
   * @param object|string $objectOrClass
   * @param int $filter 默认ReflectionProperty::IS_PUBLIC，公开属性
   * @param bool $cache 缓存结果，默认TRUE
   * @return array<string,array{name:string,type:string,rules:array<string,array>,required:bool,default:mixed,desc:string,depend:bool}>类的Public属性列表
   */
  public static function getClassPropertyShape(
    object|string $objectOrClass,
    int           $filter = ReflectionProperty::IS_PUBLIC,
    bool          $cache = true
  ): array
  {
    $class = is_object($objectOrClass) ? get_class($objectOrClass) : $objectOrClass;
    if ($cache) {
      // 判断缓存
      $shape = self::getCache($class);
      if ($shape !== null) return $shape;
    }
    // 反射运行时继承类
    try {
      $reflection = new ReflectionClass($objectOrClass);
    } catch (ReflectionException $e) {
      throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
    }
    // 拿到类属性
    $properties = $reflection->getProperties($filter);
    // 属性结构
    $shape = [];
    foreach ($properties as $property) {
      /** 属性注释 */
      $doc = $property->getDocComment();
      /** 属性名称 */
      $name = $property->getName();
      $shape[$name] = self::parseTypeShape($doc, $property);
    }
    if ($cache) self::setCache($class, $shape);
    return $shape;
  }

  /**
   * 验证数据是否符合函数、方法或类构造函数（无构造函数时判断类public属性）的参数类型结构
   *
   * @param array $data
   * @param Closure|array|string $callable
   * @return array 如果数据验证成功会返回一个数组，包含所有验证通过的数据
   * @throws ValidateException 如果数据验证失败会抛出异常
   */
  public static function validate(Closure|array|string $callable, array $data): array
  {
    $shape = self::getParamTypeShape($callable);
    return self::validateShape($shape, $data);
  }
}
