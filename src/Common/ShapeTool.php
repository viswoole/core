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

use ReflectionClass;
use ReflectionException;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;

/**
 * 类形状生成工具
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
  public static function getClassPropertyShape(
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
      // 属性说明
      $annotation = self::extractAnnotation($property->getDocComment());
      // 得到属性类型
      $propertyType = $property->getType();
      /** 属性名称 */
      $name = $property->getName();
      /** 属性默认值 */
      $default = $property->getDefaultValue();
      /** 是否允许为null */
      $required = false;
      /** 支持的类型 */
      $types = [['name' => 'mixed', 'isBuiltin' => true]];
      // 如果属性给定了类型 则处理类型
      if (!is_null($propertyType)) {
        $required = !$propertyType->allowsNull();
        if (
          $propertyType instanceof ReflectionUnionType
          || $propertyType instanceof ReflectionIntersectionType
        ) {
          // 联合类型
          $types = [];
          foreach ($propertyType->getTypes() as $typeItem) {
            $types[] = ['name' => $typeItem->getName(), 'isBuiltin' => $typeItem->isBuiltin()];
          }
        } elseif ($propertyType instanceof ReflectionNamedType) {
          // 独立的类型
          $types = [[
            'name' => $propertyType->getName(),
            'isBuiltin' => $propertyType->isBuiltin()
          ]];
        }
      }
      $shape[$name] = compact('types', 'required', 'default', 'annotation');
    }
    if ($cache) self::$cacheShape[$hash] = $shape;
    return $shape;
  }

  /**
   * 提取doc注释
   *
   * @param string $doc
   * @return string
   */
  private static function extractAnnotation(string $doc): string
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
   * 从注释中获取var类型注释
   *
   * Example usage:
   * ```
   *  // 由于注释冲突问题在示例中没有编写完整的属性注释文档结构
   *  class UserInfo{
   *    // 假设 $name属性设置了`var string 姓名`注释
   *    public string $name;
   *  }
   * // 得到是$type结果为 ['var string 姓名'，'string','姓名']
   *  $type = ShapeTool::getTypeNameFromAnnotation(UserInfo::class, 'name');
   * ```
   * @access public
   * @param string $className 类名
   * @param string $propertyName 属性名称
   * @return array|null 提取到的类型注释
   * @throws ReflectionException
   */
  public static function getTypeNameFromAnnotation(
    string $className,
    string $propertyName,
  ): ?array
  {
    $rp = new ReflectionProperty($className, $propertyName);
    if (preg_match('/@var\s+(\S+)\s+(\S+)/', $rp->getDocComment(), $matches)) {
      return $matches;
    }
    return null;
  }
}
