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

namespace ViSwoole\Core\Exception;

use Exception;
use Throwable;

/**
 * 该类用于抛出严重异常错误
 */
class BaseException extends Exception
{
  /**
   * @var int 错误级别
   */
  protected int $severity = E_ERROR;
  /**
   * @var array 额外的DEBUG数据
   */
  protected array $data = [];

  public function __construct(
    string     $message = "",
    int        $code = 0,
    int        $severity = E_ERROR,
    ?Throwable $previous = null
  )
  {
    $this->severity = $severity;
    parent::__construct($message, $code, $previous);
  }

  /**
   * 获取错误级别
   * @access public
   * @return int 错误级别
   */
  final public function getSeverity(): int
  {
    return $this->severity;
  }

  /**
   * 获取错误信息
   * @access public
   * @return array
   */
  final public function getErrorInfo()
  {
    // TODO 待实现
    return [];
  }

  /**
   * 获取debug数据
   *
   * @access public
   * @return array
   */
  final public function getData(): array
  {
    return $this->data;
  }

  /**
   * 设置异常额外的Debug数据
   * 数据将会显示为下面的格式
   *
   * Exception Data
   * --------------------------------------------------
   * Label 1
   *   key1      value1
   *   key2      value2
   * Label 2
   *   key1      value1
   *   key2      value2
   *
   * @access public
   * @param string $label 数据分类，用于异常页面显示
   * @param array $data 需要显示的数据，必须为关联数组
   */
  final public function setData(string $label, array $data): static
  {
    $this->data[$label] = $data;
    return $this;
  }
}
