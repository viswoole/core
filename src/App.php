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

/**
 * App基础容器类
 *
 * @property Env $env 环境变量管理实例
 * @property Config $config 配置管理实例
 * @property Console $console 命令行管理实例
 * @property Event $event 全局事件管理器
 */
class App extends Container
{
  protected array $bindings = [
    'app' => App::class,
    'env' => Env::class,
    'config' => Config::class,
    'console' => Console::class,
    'event' => Event::class,
    'validate' => Validate::class
  ];
  /**
   * @var ServiceProvider[] 服务列表
   */
  protected array $services = [];
  /**
   * @var bool 是否开启调试模式
   */
  protected bool $debug;

  public function __construct()
  {
    parent::__construct();
    $this->debug = $this->config->get('app.debug', false);
    $this->initialize();
  }

  /**
   * 初始化应用
   *
   * @return void
   */
  protected function initialize(): void
  {
    date_default_timezone_set($this->config->get('app.default_timezone', 'Asia/Shanghai'));
    $this->load();
    $this->event->emit('AppInit');
  }

  /**
   * 加载配置
   *
   * @return void
   */
  protected function load(): void
  {
    // 注册服务
    $services = $this->config->get('app.services', []);
    foreach ($services as $service) $this->registerService($service);
    // 启动服务
    $this->bootService();
  }

  /**
   * 注册服务
   * @access public
   * @param ServiceProvider|string $service 服务
   * @return void
   */
  protected function registerService(ServiceProvider|string $service): void
  {
    if (is_string($service)) $service = new $service($this);
    $service->register();
    if (property_exists($service, 'bindings')) {
      $this->binds($service->bindings);
    }
    $this->services[] = $service;
  }

  /**
   * 初始化服务
   *
   * @return void
   */
  protected function bootService(): void
  {
    foreach ($this->services as $service) $service->boot();
  }

  /**
   * 监听销毁
   */
  public function __destruct()
  {
    $this->event->emit('AppDestroyed');
  }

  /**
   * 获取项目根路径
   *
   * @access public
   * @return string
   */
  public function getRootPath(): string
  {
    $path = defined('BASE_PATH')
      ? BASE_PATH
      : dirname(realpath(__DIR__), 4);
    if (str_ends_with($path, '/')) $path = rtrim($path, '/');
    return $path;
  }
}
