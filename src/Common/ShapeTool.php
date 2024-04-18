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

namespace ViSwoole\Core\Common;

use Closure;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionUnionType;

/**
 * 形状解析工具
 */
final class ShapeTool
{
  /**
   * @var array 缓存解析好的结构,减少解析时间
   */
  private static array $cacheShape = [];

  /**
   * 获取类的属性结构
   *
   * Example usage:
   * ```
   * class MyClass{
   *   public string $name = 'viswoole';
   * }
   * $shape = ShapeTool::getPropertyShape(MyClass::class);
   * var_dump($shape);
   * // $shape如下
   * $shape = ['name'=>['types'=>['name'=>'string', 'isBuiltin'=>true],'required'=>false,'default'=>'viswoole','annotation'=>''];
   * ```
   * @access public
   * @param object|string $objectOrClass
   * @param int $filter 默认ReflectionProperty::IS_PUBLIC，公开属性
   * @param bool $cache 是否缓存，默认TRUE
   * @return array{
   *  string: array{
   *    types:array{
   *      name:string,
   *      isBuiltin:bool,
   *    },
   *    required:bool,
   *    default:mixed,
   *    annotation:string,
   *  }
   * } 类的Public属性结构
   * @throws ReflectionException
   */
  public static function getPropertyShape(
    object|string $objectOrClass,
    int           $filter = ReflectionProperty::IS_PUBLIC,
    bool          $cache = true
  ): array
  {
    // 反射运行时继承类
    $reflection = new ReflectionClass($objectOrClass);
    if ($cache) {
      // 获得类文件的哈希值
      $hash = hash_file('md5', $reflection->getFileName());
      // 判断缓存
      if (array_key_exists($hash, self::$cacheShape)) return self::$cacheShape[$hash];
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
    if ($cache) self::$cacheShape[$hash] = $shape;
    return $shape;
  }

  /**
   * 解析类型结构
   *
   * @return array{required:bool, types: array{name:string, isBuiltin:bool},default:mixed,annotation:string}
   */
  private static function parseTypeShape(
    string|false $doc, ReflectionProperty|ReflectionParameter $reflector
  ): array
  {
    /** 属性说明 */
    $annotation = '';
    if ($doc) {
      if ($reflector instanceof ReflectionProperty) {
        // 提取属性注释说明部分
        $annotation = self::extractPropertyTypeAnnotation($doc);
      } else {
        $annotation = self::extractParamTypeAnnotation($doc, $reflector->getName());
      }
    }
    /** 类型 */
    $type = $reflector->getType();
    try {
      /** 属性默认值 */
      $default = $reflector->getDefaultValue();
      /** 是否必填 */
      $required = false;
    } catch (ReflectionException) {
      // 如果非可选参数设置为null
      $default = null;
      // 必填参数
      $required = true;
    }
    /** 支持的类型 */
    $types = [['name' => 'mixed', 'isBuiltin' => true]];
    // 如果属性给定了类型 则处理类型
    if (!is_null($type)) {
      $required = !$reflector->allowsNull();
      if (
        $type instanceof ReflectionUnionType
        || $type instanceof ReflectionIntersectionType
      ) {
        // 联合类型
        $types = [];
        foreach ($type->getTypes() as $typeItem) {
          $types[] = ['name' => $typeItem->getName(), 'isBuiltin' => $typeItem->isBuiltin()];
        }
      } elseif ($type instanceof ReflectionNamedType) {
        // 独立的类型
        $types = [[
          'name' => $type->getName(),
          'isBuiltin' => $type->isBuiltin()
        ]];
      }
    }
    return compact('types', 'required', 'default', 'annotation');
  }

  /**
   * 从注释文档中提取到属性说明
   *
   * @param string $doc
   * @return string
   */
  private static function extractPropertyTypeAnnotation(string $doc): string
  {
    if (preg_match(
      '/@var\s+(\S+)\s+(\S+)/', $doc ?: '',
      $matches
    )) {
      $doc = end($matches);
      return $doc ?: '';
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
    if (preg_match(
      '/@param\s+(\S+)\s+(\$' . preg_quote($param_name, '/') . ')\s+(\S+)/', $doc ?: '',
      $matches
    )) {
      $doc = end($matches);
      return $doc ?: '';
    }
    return '';
  }

  /**
   * 获取函数、方法或类构造函数的参数类型结构
   *
   * Example usage:
   *  ```
   *  $shape = ShapeTool::getParamTypeShape(function (\App\DataSet\User $user){});
   *  var_dump($shape);
   *  // $shape如下
   *  $shape = ['user'=>['types'=>['name'=>'\App\DataSet\User', 'isBuiltin'=>false],'required'=>false,'default'=>null,'annotation'=>''];
   *  ```
   *
   * @access public
   * @param callable|string $callable 函数、[object|class,method]或类名称
   * @return array
   * @throws ReflectionException|InvalidArgumentException 如果$callable不正确会抛出反射异常或参数无效异常
   */
  public static function getParamTypeShape(callable|string $callable): array
  {
    if ($callable instanceof Closure) {
      $reflection = new ReflectionFunction($callable);
    } elseif (is_array($callable)) {
      $reflection = new ReflectionMethod($callable[0], $callable[1]);
    } elseif (is_string($callable)) {
      if (str_contains($callable, '::')) {
        $reflection = new ReflectionMethod($callable);
      } elseif (class_exists($callable)) {
        $reflection = (new ReflectionClass($callable))->getConstructor();
        // 如果没有构造函数 则返回空数组
        if (is_null($reflection)) return [];
      } elseif (function_exists($callable)) {
        $reflection = new ReflectionFunction($callable);
      }
    }
    if (!isset($reflection)) throw new InvalidArgumentException(
      '$callable参数类型必须是Closure|[class|object,method]|class::method|function_name|class_name'
    );
    $params = $reflection->getParameters();
    $doc = $reflection->getDocComment();
    $shape = [];
    foreach ($params as $param) {
      /** 参数名称 */
      $name = $param->getName();
      $shape[$name] = self::parseTypeShape($doc, $param);
    }
    return $shape;
  }
}
