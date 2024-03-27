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

namespace ViSwoole\Core\Contract;

use Closure;
use ViSwoole\Core\Exception\ValidateException;

/**
 * 验证器接口约束
 */
interface ValidateInterface
{
  /**
   * 数据验证
   *
   * Example usage:
   *  ```
   *  try{
   *    Validate::rule('name', 'require|max:25')->check(['name' => 'viswoole']);
   *  }catch(ValidateException $e){
   *    echo $e->getMessage(); // 如果验证失败则会抛出验证异常，通过$e->getMessage可以捕获异常提示信息
   *  }
   *  ```
   * @access public
   * @param array $data 数据
   * @param bool $batch 是否批量验证
   * @return bool
   * @throws ValidateException 验证失败会抛出异常
   */
  public function check(array $data): bool;

  /**
   * 设置验证规则
   *
   * @access public
   * @param array $rules
   * @return ValidateInterface
   */
  public function rules(array $rules): ValidateInterface;

  /**
   * 设置自定义提示
   *
   * @param array $message
   * @return ValidateInterface
   */
  public function message(array $message): ValidateInterface;

  /**
   * 设置验证场景
   *
   * @access public
   * @param string $name 验证场景
   * @return ValidateInterface
   */
  public function scene(string $name): ValidateInterface;

  /**
   * 判断是否存在某个验证场景
   *
   * @access public
   * @param string $name 场景名
   * @return false|string
   */
  public function hasScene(string $name): false|string;

  /**
   * 指定需要验证的字段列表
   *
   * @param array|string $fields 字段名
   * @return ValidateInterface
   */
  public function only(array|string $fields): ValidateInterface;

  /**
   * 移除某个字段的验证规则
   *
   * @param array|string $field 字段名
   * @param string|array|null $rule 验证规则
   * @return ValidateInterface
   */
  public function remove(array|string $field, string|array $rule = null): ValidateInterface;

  /**
   * 追加某个字段的验证规则
   *
   * @param array|string $field 字段名
   * @param array|string|Closure|null $rule 验证规则
   * @return ValidateInterface
   */
  public function append(array|string $field, array|string|Closure $rule = null): ValidateInterface;
}
