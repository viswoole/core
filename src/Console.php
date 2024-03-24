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
use Symfony\Component\Console\Command\Command;

/**
 * 命令行处理程序
 */
class Console extends Application
{
  protected array $defaultCommands = [
    \ViSwoole\Core\Command\Optimize\Facade::class
  ];

  public function __construct(string $name = 'viswoole', string $version = '1.0.0')
  {
    parent::__construct($name, $version);
    $this->loadCommand();
  }

  /**
   * 加载命令
   * @return void
   */
  protected function loadCommand(): void
  {
    $config = config('app.commands', []);
    $config = array_merge($this->defaultCommands, $config);
    foreach ($config as $class) {
      $this->add(\ViSwoole\Core\Facades\App::invokeClass($class));
    }
  }

  /**
   * 添加一个命令行处理程序
   *
   * @access public
   * @param Command $command
   * @return Command|null
   */
  public function addCommand(Command $command): ?Command
  {
    return $this->add($command);
  }
}
