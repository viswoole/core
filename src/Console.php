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

use RuntimeException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use ViSwoole\Core\Console\Commands\CommandDiscover;
use ViSwoole\Core\Console\Commands\Server\ServerClose;
use ViSwoole\Core\Console\Commands\Server\ServerReload;
use ViSwoole\Core\Console\Commands\Server\ServerStart;
use ViSwoole\Core\Console\Commands\ServiceDiscover;

/**
 * 命令行处理程序
 */
class Console extends Application
{
  protected array $defaultCommands = [
    CommandDiscover::class,
    Console\Commands\Optimize\Facade::class,
    ServiceDiscover::class,
    ServerStart::class,
    ServerClose::class,
    ServerReload::class
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
    $depPath = getRootPath() . '/vendor/commands.php';
    // 依赖包注册的服务
    $dependentCommands = is_file($depPath) ? require $depPath : [];
    // 合并
    $config = array_merge($this->defaultCommands, $config, $dependentCommands);
    foreach ($config as $class) {
      if (!class_exists($class)) throw new RuntimeException("$class 命令处理类不存在");
      if (is_subclass_of($class, Command::class)) {
        $this->addCommand(\ViSwoole\Core\Facades\App::invokeClass($class));
      } else {
        throw new RuntimeException(
          "$class 不是可用的命令行处理程序，命令处理类必须继承自 " . Command::class
        );
      }
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
