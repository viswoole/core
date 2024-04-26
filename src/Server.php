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

use Closure;
use Socket;
use Swoole\Constant;
use Swoole\Process;
use Swoole\Server as SwooleServer;
use Swoole\Server\Port;
use ViSwoole\Core\Server\EventHandle;
use ViSwoole\Core\Server\Exception\ServerException;
use ViSwoole\Core\Server\Exception\ServerNotFoundException;
use ViSwoole\Core\Server\HookEventHandler;
use ViSwoole\Core\Server\Task;
use ViSwoole\HttpServer\Exception\Handle;

/**
 * Swoole服务代理类
 *
 * @link https://wiki.swoole.com/zh-cn/#/server/methods
 *
 * @method Port|\false listen(string $host, int $port, int $sock_type)
 * @method Port|\false addlistener(string $host, int $port, int $sock_type)
 * @method bool on(string $event_name, callable $callback)
 * @method Closure|array|string|null getCallback(string $event_name)
 * @method bool set(array $settings)
 * @method bool send(string|int $fd, string $send_data, int $serverSocket = -1)
 * @method bool sendto(string $ip, int $port, string $send_data, int $server_socket = -1)
 * @method bool sendwait(int $conn_fd, string $send_data)
 * @method bool exists(int $fd)
 * @method bool exist(int $fd)
 * @method bool protect(int $fd, bool $is_protected = true)
 * @method bool sendfile(int $conn_fd, string $filename, int $offset = 0, int $length = 0)
 * @method bool close(int $fd, bool $reset = false)
 * @method bool confirm(int $fd)
 * @method bool pause(int $fd)
 * @method bool resume(int $fd)
 * @method int|false task(mixed $data, int $taskWorkerIndex = -1, ?callable $finishCallback = null)
 * @method mixed taskwait(mixed $data, float $timeout = 0.5, int $taskWorkerIndex = -1)
 * @method array|false taskWaitMulti(array $tasks, float $timeout = 0.5)
 * @method array|false taskCo(array $tasks, float $timeout = 0.5)
 * @method bool finish(mixed $data)
 * @method bool reload(bool $only_reload_taskworker = false)
 * @method bool shutdown()
 * @method bool stop(int $workerId = -1, bool $waitEvent = false)
 * @method int getLastError()
 * @method array|false heartbeat(bool $ifCloseConnection = true)
 * @method array|false getClientInfo(int $fd, int $reactor_id = -1, bool $ignoreError = false)
 * @method array|false getClientList(int $start_fd = 0, int $find_count = 10)
 * @method int|false getWorkerId()
 * @method int|false getWorkerPid(int $worker_id = -1)
 * @method int|false getWorkerStatus(int $worker_id = -1)
 * @method int getManagerPid()
 * @method int getMasterPid()
 * @method array|false connection_info(int $fd, int $reactor_id = -1, bool $ignoreError = false)
 * @method array|false connection_list(int $start_fd = 0, int $find_count = 10)
 * @method bool sendMessage(mixed $message, int $dst_worker_id)
 * @method array|string|false command(string $name, int $process_id, int $process_type, mixed $data, bool $json_decode = true)
 * @method bool addCommand(string $name, int $accepted_process_types, callable $callback)
 * @method int addProcess(Process $process)
 * @method array stats()
 * @method Socket|false getSocket(int $port = 0)
 * @method bool bind(int $fd, int $uid)
 */
class Server
{
  /**
   * @var string 服务名称
   */
  public readonly string $serverName;
  /**
   * @var array 服务配置
   */
  protected array $config;
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
  /**
   * @var array 默认全局配置
   */
  protected array $global_option;
  /**
   * @var array 默认全局事件处理
   */
  protected array $global_event;
  /**
   * @var bool 服务是否已启动
   */
  private bool $isStart = false;
  /**
   * @var SwooleServer swoole服务实例
   */
  private SwooleServer $server;

  protected function __construct(string $server_name)
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
      'server.default_pid_store_dir',
      $this->rootPath . '/runtime/server_pid'
    );
    $default_global_option = [
      // 一键协程化Hook函数范围 参考https://wiki.swoole.com/#/server/setting?id=hook_flags
      Constant::OPTION_HOOK_FLAGS => SWOOLE_HOOK_ALL,
      // 是否启用异步风格服务器的协程支持
      Constant::OPTION_ENABLE_COROUTINE => true,
      // 最大协程数
      Constant::OPTION_MAX_CONCURRENCY => 10000,
      // 进程守护运行
      Constant::OPTION_DAEMONIZE => false,
      // 进程守护运行默认输出日志路径
      Constant::OPTION_LOG_FILE => $this->rootPath . '/runtime/swoole.log',
      // 工作进程数量
      Constant::OPTION_WORKER_NUM => swoole_cpu_num(),
      // 最大请求数 0为不限制
      Constant::OPTION_MAX_REQUEST => 0,
      // 客户端连接的缓存区长度
      Constant::OPTION_SOCKET_BUFFER_SIZE => 2 * 1024 * 1024,
      // 发送输出缓冲区内存尺寸
      Constant::OPTION_BUFFER_OUTPUT_SIZE => 2 * 1024 * 1024,
      // 数据包最大尺寸 最小64k
      Constant::OPTION_PACKAGE_MAX_LENGTH => 2 * 1024 * 1024,
      // 日志输出等级
      Constant::OPTION_LOG_LEVEL => SWOOLE_LOG_WARNING
    ];
    // 全局配置
    $this->global_option = array_merge($default_global_option, config('server.options', []));
    $default_global_event = [
      Constant::EVENT_START => [EventHandle::class, 'onStart'],
      Constant::EVENT_SHUTDOWN => [EventHandle::class, 'onShutdown']
    ];
    // 全局监听
    $this->global_event = array_merge($default_global_event, config('server.events', []));
    // 初始化加载服务配置
    $this->config = $this->getConfig();
    // 创建服务
    $this->server = $this->createSwooleServer();
  }

  /**
   * 加载服务配置
   *
   * @return array
   */
  public function getConfig(): array
  {
    if (isset($this->config)) return $this->config;
    $config = config("server.servers.$this->serverName");
    if (empty($config)) {
      throw new ServerNotFoundException(
        "{$this->serverName}服务未定义，请检查$this->rootPath/config/autoload/server.php配置文件。"
      );
    }
    if (!($config['type'] ?? '' instanceof SwooleServer)) {
      throw new ServerNotFoundException(
        "{$this->serverName}服务type属性配置错误，请检查$this->rootPath/config/autoload/server.php配置文件。"
      );
    }
    // 判断异常处理方法
    if (!isset($config['exception_handle'])) $config['exception_handle'] = $this->default_exception_handle;

    // 服务构造参数
    $config['construct'] = array_merge(
      $this->defaultConstructArguments, $config['construct'] ?? []
    );
    // 合并配置
    $config['options'] = array_merge($this->global_option, $config['options'] ?? []);
    // 合并事件监听
    $events = array_merge($this->global_event, $config['events'] ?? []);
    // HOOK事件监听
    $config['events'] = HookEventHandler::hook($events);
    // 任务回调协程化
    $config['options'][Constant::OPTION_TASK_ENABLE_COROUTINE] = true;
    // 判断PID存储路径是否设置
    if (empty($config['options'][Constant::OPTION_PID_FILE])) {
      $config['options'][Constant::OPTION_PID_FILE] = $this->default_pid_store_dir . "/$this->serverName.pid";
    }
    // 判断PID存储路径是否存在，如果不存在则创建
    $pid_file = $config['options'][Constant::OPTION_PID_FILE];
    // 获取目录路径
    $directory = dirname($pid_file);
    // 检查目录是否存在，如果不存在则创建它
    if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
      die("无法创建PID目录: $directory");
    }
    return $config;
  }

  /**
   * 创建服务
   *
   * @return SwooleServer
   */
  protected function createSwooleServer(): SwooleServer
  {
    /**
     * @var SwooleServer $server
     */
    $server = new $this->config['type'](...$this->config['construct']);
    $events = $this->config['events'] ?? [];
    if (isset($events[Constant::EVENT_TASK])) {
      if (
        $events[Constant::EVENT_TASK] === Task::class . '::' . 'dispatch'
        || $events[Constant::EVENT_TASK] === [Task::class, 'dispatch']
      ) {
        $this->config['options'][Constant::OPTION_TASK_USE_OBJECT] = true;
        // 启动任务管理器
        Task::factory();
      }
    }
    // 设置配置
    $server->set($this->config['options']);
    // 注册监听事件
    foreach ($events as $event_name => $handler) {
      $server->on($event_name, $handler);
    }
    return $server;
  }

  /**
   * 工厂单例模式
   */
  public static function factory(string $server_name = null): static
  {
    static $instance = null;
    if (empty($server_name) && $instance === null) {
      throw new ServerException('请传入要创建的服务名称');
    }
    if ($instance === null) $instance = new static($server_name);
    return $instance;
  }

  /**
   * 容器make实例化
   */
  public static function __make(): static
  {
    return self::factory();
  }

  public static function __callStatic(string $name, array $arguments)
  {
    return call_user_func_array([self::factory(), $name], $arguments);
  }

  /**
   * 启动服务
   *
   * @access public
   * @return bool
   */
  public function start(): bool
  {
    $serverName = $this->serverName;
    if ($this->isStart) throw new ServerException("{$serverName}服务已在运行中，请勿重复启动服务。");
    $this->isStart = true;
    // 触发ServerStartBefore事件
    Event::factory()->emit('ServerStartBefore', [$this]);
    $result = $this->server->start();
    $this->isStart = $result;
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
   * 判断服务是否已经启动
   *
   * @access public
   * @return bool
   */
  public function isStart(): bool
  {
    return $this->isStart;
  }

  public function __call(string $name, array $arguments)
  {
    return call_user_func_array([$this->getServer(), $name], $arguments);
  }

  /**
   * 获取Swoole\Server原型实例
   *
   * @access public
   * @return SwooleServer
   */
  public function getServer(): SwooleServer
  {
    return $this->server;
  }
}
