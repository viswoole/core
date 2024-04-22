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

namespace ViSwoole\Core\Server;

use Swoole\Process;
use ViSwoole\Core\Server;
use ViSwoole\Core\Server\Exception\ServerException;

/**
 * Swoole服务操作类
 */
class ServerAction
{
  public static function start(string $server_name, bool $forceStart = false): bool
  {
    $pid = self::getServerPid($server_name);
    if ($pid) {
      if ($forceStart) {
        Process::kill($pid, SIGTERM);
      } else {
        throw new ServerException("{$server_name}服务已经在运行中，请勿重复启动。");
      }
    }
    return Server::factory($server_name)->startServer();
  }

  /**
   * 获取服务进程PID
   *
   * @access public
   * @param string $server_name 服务名称
   * @return false|int 如果未运行返回false
   */
  public static function getServerPid(string $server_name): false|int
  {
    $pid_file = config("server.servers.$server_name.options.pid_file");
    if (empty($pid_file)) {
      $pid_file = config(
        'default_pid_store_dir',
        getRootPath() . '/runtime/server_pid'
      );
    }
    //读取服务进程id 判断服务是否正在运行
    $pid = null;
    $status = false;
    if (is_file($pid_file)) {
      // 获取PID内容
      $file_content = file_get_contents($pid_file);
      if (!empty($file_content)) {
        $pid = (int)$file_content;
        // 判断进程是否正在运行
        $status = Process::kill($pid, 0);
        // 如果没有运行则删除pid文件
        if (!$status) unlink($pid_file);
      }
    }
    return $status ? $pid : false;
  }

  /**
   * 获取服务状态
   *
   * @access public
   * @param string $server_name 服务名称
   * @return bool
   */
  public static function getStatus(string $server_name): bool
  {
    return is_int(self::getServerPid($server_name));
  }

  /**
   * 安全停止服务
   *
   * @access public
   * @param string $server_name
   * @return void
   */
  public static function close(string $server_name): void
  {
    $pid = self::getServerPid($server_name);
    if ($pid) {
      $status = Process::kill($pid, SIGTERM);
      if (!$status) throw new ServerException("{$server_name}服务停止运行失败");
    }
  }

  /**
   * 重启服务
   *
   * @access public
   * @param string $server_name
   * @param bool $only_reload_task_worker 是否只重启任务进程
   * @return void
   */
  public static function reload(string $server_name, mixed $only_reload_task_worker = false): void
  {
    $pid = self::getServerPid($server_name);
    if ($pid) {
      $status = Process::kill($pid, $only_reload_task_worker ? SIGUSR2 : SIGUSR1);
      if (!$status) throw new ServerException("{$server_name}服务重启失败");
    }
  }
}
