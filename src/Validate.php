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

use Closure;
use InvalidArgumentException;
use ViSwoole\Core\Contract\ValidateInterface;
use ViSwoole\Core\Exception\ValidateException;
use ViSwoole\Core\Validate\ParseRule;
use ViSwoole\Core\Validate\ValidateExceptionMessage;
use ViSwoole\Core\Validate\ValidateRule;

/**
 * 数据验证器
 *
 * 可继承此类实现自定义的验证器
 */
class Validate
{
  use ParseRule;
  use ValidateExceptionMessage;

  /**
   * @var array 场景需要移除的验证规则
   */
  private array $remove = [];
  /**
   * @var array 场景需要追加的验证规则
   */
  private array $append = [];
  /**
   * @var string 当前验证场景
   */
  private string $currentScene;
  /**
   * @var string 最后一次验证场景
   */
  private string $lastScene;
  /**
   * @var array 场景需要验证的字段
   */
  private array $onlyFields;

  /**
   * 数据验证
   *
   * Example usage:
   *  ```
   *  try{
   *    // 门面类快捷验证
   *    \ViSwoole\Core\Facades\Validate::rule('name', 'require|max:25')->check(['name' => 'viswoole']);
   *    // 验证器验证
   *    $validate = new \ViSwoole\Core\Validate();
   *    $validate->check(['name' => 'viswoole']);//check方法第二个参数传入true则批量验证
   *  }catch(ValidateException $e){
   *    echo $e->getMessage(); // 如果验证失败则会抛出验证异常，通过$e->getMessage可以捕获异常提示信息
   *  }
   *  ```
   * @access public
   * @param array $data 数据
   * @param bool $batch 是否批量验证
   * @return bool
   */
  public function check(array $data, bool $batch = false): bool
  {
    if (!isset($this->rules)) throw new ValidateException('验证规则不能为空');

    if (isset($this->currentScene)) {
      if ($this->currentScene !== $this->lastScene) {
        // 重置场景验证器
        $this->reset();
        $this->lastScene = $this->currentScene;
        $this->{$this->currentScene}();
        unset($this->currentScene);
      }
    }
    $results = [];
    foreach ($this->rules as $field => $structure) {
      // 如果设置了只验证的字段则判断，则判断当前字段是否需要验证。
      if (isset($this->onlyFields) && !in_array($field, $this->onlyFields)) continue;
      // 字段别名或描述
      $alias = $structure['alias'];
      // 拿到待验证的规则
      $rules = is_array($structure['rules'])
        ? $this->rulesToBeVerified($field, $structure['rules'])
        : $structure['rules'];
      // 开始验证
      if ($rules instanceof Closure) {
        // 如果是闭包则直接验证
        try {
          $result = $rules($data[$field]);
          if ($result !== true) {
            $message = is_string($result) ? $result : "$alias 验证失败";
            $result = false;
          }
        } catch (ValidateException $e) {
          $result = false;
          $message = $e->getMessage();
        }
        // 如果非批量验证则抛出异常
        if (!$result) {
          if (!$batch) {
            throw new ValidateException($message);
          } else {
            $results[$field] = $message;
          }
        }
      } else {
        $value = $data[$field] ?? null;
        foreach ($rules as $validate => $filter) {
          if (!ValidateRule::$validate($value, $filter)) {
            $message = $this->getErrorMessage(
              $field,
              $alias,
              $validate,
              $filter,
              ValidateRule::getError($validate)
            );
            if (!$batch) {
              throw new ValidateException($message);
            } else {
              $results[$field] = $message;
              break;
            }
          }
        }
      }
    }
    if (empty($results)) return true;
    throw new ValidateException($results);
  }

  /**
   * 重置验证器为初始状态
   * @return void
   */
  private function reset(): void
  {
    $this->remove = [];
    $this->append = [];
    unset($this->onlyFields);
  }

  /**
   * 生成最终待验证的规则
   *
   * @param string $field 字段
   * @param array $rules 规则
   * @return array
   */
  private function rulesToBeVerified(string $field, array $rules): array
  {
    if (!empty($this->remove) && !empty($this->remove[$field])) {
      foreach ($this->remove[$field] as $rule) {
        unset($rules[$rule]);
      }
    }
    if (!empty($this->append) && !empty($this->append[$field])) {
      $rules = array_merge_recursive($rules, $this->append[$field]);
    }
    return $rules;
  }

  /**
   * 设置验证规则
   *
   * @access public
   * @param array $rules 验证规则格式：['field1|字段描述,field2'=>'rule1|rule2...' | ['rule1'=>[]...] |Closure ]
   * @return ValidateInterface
   */
  public function rules(array $rules): ValidateInterface
  {
    $this->rules = $this->parseRules($rules);
    return $this;
  }

  /**
   * 设置自定义提示
   *
   * @param array $message
   * @return ValidateInterface
   */
  public function message(array $message): ValidateInterface
  {
    $this->message = $message;
    return $this;
  }

  /**
   * 设置验证场景
   *
   * @access public
   * @param string $name 验证场景
   * @return ValidateInterface
   */
  public function scene(string $name): ValidateInterface
  {
    $scene = $this->hasScene($name);
    if (!$scene) throw new InvalidArgumentException('验证场景' . $name . ' 不存在');
    $this->currentScene = $scene;
    return $this;
  }

  /**
   * 判断是否存在某个验证场景
   *
   * @access public
   * @param string $name 场景名
   * @return false|string
   */
  private function hasScene(string $name): false|string
  {
    $name = 'scene' . ucfirst($name);
    return method_exists($this, $name) ? $name : false;
  }

  /**
   * 指定需要验证的字段列表
   *
   * @param array|string $fields 字段名
   * @return ValidateInterface
   */
  protected function only(array|string $fields): ValidateInterface
  {
    if (is_string($fields)) $fields = explode('|', $fields);
    $this->onlyFields = $fields;
    return $this;
  }

  /**
   * 移除验证规则
   *
   * @param array|string $field 字段名或关联数组键为字段值为要删除的规则
   * @param array|string|null $rule 验证规则 [rule1,rule2...]|'rule1,rule2...'
   * @return ValidateInterface
   */
  protected function remove(string|array $field, array|string $rule = null
  ): ValidateInterface
  {
    if (is_array($field)) {
      foreach ($field as $key => $value) {
        $this->remove($key, $value);
      }
    } else {
      $rule = str_replace(' ', '', $rule);
      $this->remove[$field] = is_string($rule)
        ? explode(',', $rule)
        : $rule;
    }
    return $this;
  }

  /**
   * 追加某个字段的验证规则
   *
   * @param array|string $field 字段名
   * @param string|array|Closure|null $rule 要追加的验证规则
   * @return ValidateInterface
   */
  protected function append(array|string $field, array|string|Closure $rule = null
  ): ValidateInterface
  {
    if (is_array($field)) {
      foreach ($field as $key => $value) {
        $this->append($key, $value);
      }
      return $this;
    }
    $this->append[$field] = $this->parseRule($rule);
    return $this;
  }
}
