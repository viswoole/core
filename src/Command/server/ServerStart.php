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

namespace ViSwoole\Core\Command\server;

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
 * 启动服务
 */
#[AsCommand(
  name       : 'server:start',
  description: 'Start a server.',
  hidden     : false
)]
class ServerStart extends Command
{
  protected function configure(): void
  {
    $this->addArgument(
      'service',
      InputArgument::REQUIRED,
      'Name of the service to start'
    );
    $this->addOption(
      'force',
      'f',
      InputOption::VALUE_NONE,
      'Force the service to start'
    );
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $service = $input->getArgument('service');
    $force = $input->getOption('force');
    $io = new SymfonyStyle($input, $output);
    try {
      ServerAction::start($service, $force);
    } catch (Throwable $e) {
      $io->error($e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
      return Command::FAILURE;
    }
    return Command::SUCCESS;
  }
}
