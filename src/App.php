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

use ViSwoole\Core\Contract\RequestInterface;
use ViSwoole\Core\Contract\ResponseInterface;
use ViSwoole\Log\LogManager;

/**
 * App基础容器类
 *
 * @property Env $env 环境变量管理实例
 * @property Config $config 配置管理实例
 * @property Console $console 命令行管理实例
 * @property Event $event 全局事件管理器
 * @property Validate $validate 验证器
 * @property Server $server 服务管理实例
 * @property MiddlewareManager $middleware 服务管理实例
 * @property LogManager $log 日志管理实例
 */
class App extends Container
{
  protected array $bindings = [
    'app' => App::class,
    'env' => Env::class,
    'config' => Config::class,
    'console' => Console::class,
    'log' => LogManager::class,
    'event' => Event::class,
    'validate' => Validate::class,
    'server' => Server::class,
    'middleware' => MiddlewareManager::class,
  ];
  /**
   * @var array 定义需要排除的类/接口，每次通过容器反射执行该类时都会重新实例化
   */
  protected array $exclude = [
    RequestInterface::class,
    ResponseInterface::class
  ];
  /**
   * @var ServiceProvider[] 服务列表
   */
  protected array $services = [];

  protected function __construct()
  {
    parent::__construct();
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
    $this->registerService();
    // 启动服务
    $this->bootService();
  }

  /**
   * 注册服务
   *
   * @access protected
   * @return void
   */
  protected function registerService(): void
  {
    $services = $this->config->get('app.services', []);
    $depPath = $this->getVendorPath() . '/services.php';
    // 依赖包注册的服务
    $dependentServices = is_file($depPath) ? require $depPath : [];
    $services = array_merge($services, $dependentServices);
    // 遍历服务绑定进容器
    foreach ($services as $service) {
      if (is_string($service)) $service = new $service($this);
      $service->register();
      if (property_exists($service, 'bindings')) $this->binds($service->bindings);
      $this->services[] = $service;
    }
  }

  /**
   * 获取vendor路径
   *
   * @return string
   */
  public function getVendorPath(): string
  {
    return $this->getRootPath() . '/vendor';
  }

  /**
   * 获取项目根路径
   *
   * @access public
   * @return string
   */
  public function getRootPath(): string
  {
    !defined('BASE_PATH') && define('BASE_PATH', dirname(realpath(__DIR__), 4));
    return rtrim(BASE_PATH, '/');
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
   * 是否debug调试模式
   *
   * @return bool
   */
  public function isDebug(): bool
  {
    return $this->config->get('app.debug', false);
  }

  /**
   * 设置是否启用debug模式
   *
   * @access public
   * @param bool $debug
   * @return void
   */
  public function setDebug(bool $debug): void
  {
    $this->config->set('app.debug', $debug);
  }

  /**
   * 监听销毁
   */
  public function __destruct()
  {
    $this->event->emit('AppDestroyed');
  }
}
