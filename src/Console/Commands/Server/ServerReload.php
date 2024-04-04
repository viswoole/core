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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;
use ViSwoole\Core\Server\ServerAction;

/**
 * 重启服务
 */
#[AsCommand(
  name       : 'server:reload',
  description: 'Reload a server or task.',
  hidden     : false
)]
class ServerReload extends Command
{
  protected function configure(): void
  {
    $this->addArgument(
      'service',
      InputArgument::REQUIRED,
      'Name of the service to reload'
    );
    $this->addOption(
      'task',
      't',
      InputOption::VALUE_NONE,
      'Reload only the task process'
    );
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $service = $input->getArgument('service');
    $taskReload = $input->getOption('task');
    $io = new SymfonyStyle($input, $output);
    try {
      ServerAction::reload($service, $taskReload);
    } catch (Throwable $e) {
      $io->error($e->getMessage());
      return Command::FAILURE;
    }
    $io->success("{$service}服务重启成功");
    return Command::SUCCESS;
  }
}
