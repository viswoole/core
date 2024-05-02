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

namespace ViSwoole\Core\Validate\Contract;

use ViSwoole\Core\Exception\ValidateException;

/**
 * 验证规则接口
 */
interface RuleInterface
{
  /**
   * 验证数据
   *
   * @param mixed $value 需要验证的数据
   * @param string $key 当前正在检测的属性或参数名称
   * @return void
   * @throws ValidateException 验证失败抛出异常
   */
  public function validate(string $key, mixed &$value): void;
}
