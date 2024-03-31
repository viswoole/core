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

namespace ViSwoole\Core\Command;

use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * 发现服务
 */
#[AsCommand(
  name       : 'service:discover',
  description: 'Automatically scans the services provided in the dependency package and generates a service registration file.',
  hidden     : false
)]
class ServiceDiscover extends Command
{
  #[Override] protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $path = getRootPath() . '/vendor/composer/installed.json';
    $io = new SymfonyStyle($input, $output);
    if (is_file($path)) {
      $packages = json_decode(file_get_contents($path), true);
      // Compatibility with Composer 2.0
      if (isset($packages['packages'])) $packages = $packages['packages'];

      $services = [];
      foreach ($packages as $package) {
        if (!empty($package['extra']['viswoole']['services'])) {
          $services = array_merge($services, (array)$package['extra']['viswoole']['services']);
        }
      }

      $header = '// 此文件为自service:discover命令自动生成的服务注册文件:' . date(
          'Y-m-d H:i:s'
        ) . PHP_EOL
        . 'declare (strict_types = 1);' . PHP_EOL;

      $content = '<?php ' . PHP_EOL . $header . 'return ' . var_export($services, true) . ';';

      file_put_contents(getRootPath() . 'vendor/services.php', $content);

      $io->success('已在 vendor/services.php 中生成服务注册文件');
    }
    return Command::SUCCESS;
  }
}
