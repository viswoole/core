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

use Swoole\Constant;
use Swoole\Server as SwooleServer;
use ViSwoole\Core\Exception\Handle;
use ViSwoole\Core\Server\Exception\ServerException;
use ViSwoole\Core\Server\Exception\ServerNotFoundException;
use ViSwoole\Core\Server\HookEventHandler;

/**
 * Swoole服务管理
 */
class Server
{
  /**
   * @var string 服务名称
   */
  protected string $serverName;
  /**
   * @var bool 当前服务状态
   */
  protected bool $status = false;
  /**
   * @var int 启动时间
   */
  protected int $timestamp;
  /**
   * @var array 服务
   */
  protected array $config = [];
  /**
   * @var SwooleServer swoole服务实例
   */
  protected SwooleServer $server;
  /**
   * @var array 默认构造参数
   */
  protected array $defaultConstructArguments = [
    //指定监听的 ip 地址。
    'host' => '0,0,0,0',
    //指定监听的端口
    'port' => 9501,
    //运行模式
    'mode' => SWOOLE_PROCESS,
    // Server 的类型
    'sock_type' => SWOOLE_SOCK_TCP,
  ];
  /**
   * @var string 默认异常处理类
   */
  protected string $default_exception_handle;
  /**
   * @var string PID默认存储目录
   */
  protected string $default_pid_store_dir;
  /**
   * @var string 项目根目录
   */
  protected string $rootPath;

  protected function __construct(string $server_name)
  {
    // 初始化加载服务配置
    $this->config = $this->initServerConfig($server_name);
    // 创建服务
    $this->server = $this->createSwooleServer();
  }

  /**
   * 加载服务配置
   *
   * @param string $server_name 服务名称
   * @return array
   * @throws ServerNotFoundException 服务未定义时触发
   */
  protected function initServerConfig(string $server_name): array
  {
    $this->serverName = $server_name;
    /**当前进程中的服务名称*/
    !defined('SERVER_NAME') && define('SERVER_NAME', $server_name);
    // 获取项目根目录
    $this->rootPath = getRootPath();
    // 配置默认异常处理类
    $this->default_exception_handle = config(
      'server.default_exception_handle',
      Handle::class
    );
    // 配置默认PID存储目录
    $this->default_pid_store_dir = config(
      'default_pid_store_dir',
      $this->rootPath . '/runtime/server_pid'
    );
    $server = config("server.servers.$server_name");
    if (empty($server)) {
      throw new ServerNotFoundException(
        "{$server_name}服务未定义，请检查$this->rootPath/config/autoload/server.php配置文件。"
      );
    }
    if (!($server['type'] ?? '' instanceof SwooleServer)) {
      throw new ServerNotFoundException(
        "{$server_name}服务type属性配置错误，请检查$this->rootPath/config/autoload/server.php配置文件。"
      );
    }
    // 判断异常处理方法
    if (!isset($server['exception_handle'])) $server['exception_handle'] = $this->default_exception_handle;
    // 全局配置
    $globalOption = config('server.options', []);
    // 全局监听
    $globalEvent = config('server.events', []);
    // 服务构造参数
    $server['construct'] = array_merge(
      $this->defaultConstructArguments, $server['construct'] ?? []
    );
    // 合并配置
    $server['options'] = array_merge($globalOption, $server['options'] ?? []);
    // 合并事件监听
    $events = array_merge($globalEvent, $server['events'] ?? []);
    // HOOK事件监听
    $server['events'] = HookEventHandler::hook($events);
    // 任务回调协程化
    $server['options'][Constant::OPTION_TASK_ENABLE_COROUTINE] = true;
    // 判断PID存储路径是否设置
    if (empty($server['options'][Constant::OPTION_PID_FILE])) {
      $server['options'][Constant::OPTION_PID_FILE] = $this->default_pid_store_dir . "/$server_name.pid";
    }
    // 判断PID存储路径是否存在，如果不存在则创建
    $pid_file = $server['options'][Constant::OPTION_PID_FILE];
    // 获取目录路径
    $directory = dirname($pid_file);
    // 检查目录是否存在，如果不存在则创建它
    if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
      die("无法创建目录: $directory");
    }
    return $server;
  }

  /**
   * 创建服务
   *
   * @return SwooleServer
   */
  protected function createSwooleServer(): SwooleServer
  {
    // 实例化swoole服务
    $server = new $this->config['type'](...$this->config['construct']);
    // 设置配置
    $server->set($this->config['options']);
    $events = $this->config['events'] ?? [];
    // 注册监听事件
    foreach ($events as $event_name => $handler) {
      $server->on($event_name, $handler);
    }
    return $server;
  }

  /**
   * 工厂单例
   */
  public static function __make(string $server_name): Server
  {
    static $instance = null;
    if ($instance === null) {
      $instance = new static($server_name);
    }
    return $instance;
  }

  /**
   * 获取服务完整配置
   *
   * @access public
   * @return array
   */
  public function getConfig(): array
  {
    return $this->config;
  }

  /**
   * 启动服务
   *
   * @access public
   * @return bool
   */
  public function startServer(): bool
  {
    $serverName = $this->serverName;
    if ($this->status) throw new ServerException("{$serverName}服务已在运行中，请勿重复启动服务。");
    $this->status = true;
    $result = $this->server->start();
    $this->status = $result;
    if (!$result) throw new ServerException("{$serverName}服务启动失败");
    return true;
  }

  /**
   * 获取服务名称
   *
   * @return string
   */
  public function getName(): string
  {
    return $this->serverName;
  }

  /**
   * 获取服务运行状态
   *
   * @access public
   * @return bool 返回true表示服务正在运行，反之亦然
   */
  public function getStatus(): bool
  {
    return $this->status;
  }

  /**
   * 获取服务实例
   *
   * @access public
   * @return SwooleServer
   */
  public function getServer(): SwooleServer
  {
    return $this->server;
  }
}
