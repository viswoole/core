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

namespace ViSwoole\Core\Console\Commands\Server;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;
use ViSwoole\Core\Server\ServerAction;

/**
 * 关闭服务
 */
#[AsCommand(
  name       : 'server:close',
  description: 'Close a server.',
  hidden     : false
)]
class ServerClose extends Command
{
  protected function configure(): void
  {
    $this->addArgument(
      'server',
      InputArgument::REQUIRED,
      'Name of the server to close'
    );
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $service = $input->getArgument('server');
    $io = new SymfonyStyle($input, $output);
    try {
      ServerAction::close($service);
    } catch (Throwable $e) {
      $io->error($e->getMessage());
      return Command::FAILURE;
    }
    $io->success("{$service}服务停止运行成功");
    return Command::SUCCESS;
  }
}

