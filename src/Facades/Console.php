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

namespace ViSwoole\Core\Facades;

use Override;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use ViSwoole\Core\Facade;

/**
 * 命令行处理程序
 *
 * @method static int run(?InputInterface $input, ?OutputInterface $output) Runs the current application.
 * @method static void addCommands(array $commands) 注册多个处理程序实例
 * @method static void addCommand(Command $command) 注册一个处理程序实例
 * 优化命令：php viswoole optimize:facade ViSwoole\\Core\\Facades\\Console
 */
class Console extends Facade
{

  /**
   * 获取当前Facade对应类名
   *
   * @access protected
   * @return string
   */
  #[Override] protected static function getFacadeClass(): string
  {
    return \ViSwoole\Core\Console::class;
  }
}
