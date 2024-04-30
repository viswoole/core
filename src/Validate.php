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
use ViSwoole\Core\Validate\ValidateRules;
use ViSwoole\Core\Validate\ValidateTrait;

/**
 * 数据验证器
 *
 * 可继承此类实现自定义的验证器
 */
class Validate implements ValidateInterface
{
  use ValidateTrait;

  /**
   * @var array 验证规则
   */
  protected array $rules = [];
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
   * @inheritDoc
   */
  public function check(array $data, bool $batch = false): array
  {
    if (empty($this->rules)) throw new ValidateException('验证规则不能为空');
    $this->resetScene();
    $newData = [];
    $results = [];
    foreach ($this->rules as $field => $metadata) {
      // 如果设置了只验证的字段则判断，则判断当前字段是否需要验证。
      if (isset($this->onlyFields) && !in_array($field, $this->onlyFields)) continue;
      // 拿到待验证的规则 [rule=>params] | 闭包函数
      $metadata['rules'] = is_array($metadata['rules'])
        ? $this->rulesToBeVerified($field, $metadata['rules'])
        : $metadata['rules'];
      try {
        $newData[$field] = $this->validateField($field, $data[$field] ?? null, $metadata);
      } catch (ValidateException $e) {
        if ($batch) {
          $results[$field] = $e->getMessage();
        } else {
          throw $e;
        }
      }
    }
    if (!empty($results)) throw new ValidateException($results);
    return $newData;
  }

  /**
   * 重置验证器为初始状态
   * @return void
   */
  private function resetScene(): void
  {
    if (isset($this->currentScene)) {
      if ($this->currentScene !== $this->lastScene) {
        // 重置场景验证器
        $this->remove = [];
        $this->append = [];
        unset($this->onlyFields);
        $this->lastScene = $this->currentScene;
        $this->{$this->currentScene}();
        unset($this->currentScene);
      }
    }
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
    $this->rules = ValidateRules::parseRules($rules);
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
    $this->append[$field] = ValidateRules::parseRule($rule);
    return $this;
  }
}
