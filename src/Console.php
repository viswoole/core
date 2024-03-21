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

use Symfony\Component\Console\Application;

/**
 * 命令行处理程序
 */
class Console extends Application
{
  public function __construct(string $name = 'viswoole', string $version = '1.0.0')
  {
    parent::__construct($name, $version);
  }
}
