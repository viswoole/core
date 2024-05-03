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

namespace ViSwoole\Core\Router;

use ArrayAccess;
use Closure;
use InvalidArgumentException;
use Override;
use RuntimeException;

/**
 * 路线配置类
 */
abstract class RouteAbstract implements ArrayAccess
{
  /**
   * @var array 路由可选配置选项
   */
  protected array $options = [
    // 路由访问路径
    'paths' => null,
    // 路由描述
    'describe' => '',
    // 处理方法
    'handler' => null,
    // 请求方式
    'method' => ['*'],
    // 请求参数验证
    'params' => [],
    // 路由中间件
    'middleware' => [],
    // 伪静态后缀校验，例如html
    'suffix' => ['*'],
    // 域名路由
    'domain' => ['*'],
    // 变量正则表达式
    'pattern' => [],
  ];

  /**
   * @param string|array $paths
   * @param callable|array $handler
   * @param array|null $parentOption
   */
  public function __construct(
    string|array          $paths,
    callable|string|array $handler,
    array                 $parentOption = null
  )
  {
    if (is_array($parentOption)) {
      $this->options = $parentOption;
      $this->options['describe'] = '';
    } else {
      $this->suffix(config('router.suffix', ['*']));
      $this->domain(config('router.domain', []));
    }
    $this->paths($paths);
    $this->handler($handler);
  }

  /**
   * 设置伪静态扩展
   * @param string|array $suffix *代表允许所有后缀
   * @return $this
   */
  public function suffix(string|array $suffix): static
  {
    $this->options['suffix'] = is_string($suffix) ? [$suffix] : $suffix;
    return $this;
  }

  /**
   * 设置域名检测
   *
   * @param string|array $domains
   * @return static
   */
  public function domain(string|array $domains): static
  {
    $domains = is_string($domains) ? [$domains] : $domains;
    $this->options['domain'] = $domains;
    return $this;
  }

  /**
   * 路由path
   *
   * @param string|array $paths
   * @return void
   */
  protected function paths(string|array $paths): void
  {
    $case = config('router.case_sensitive', false);
    if (is_string($paths)) $paths = [$paths];
    $pattern = [];
    foreach ($paths as &$path) {
      if (RouteCollector::isVariable($path)) {
        $segments = explode('/', trim($path, '/'));
        foreach ($segments as $segment) {
          if (RouteCollector::isVariable($segment)) {
            $name = RouteCollector::extractVariableName($segment);
            $pattern[$name] = $this->options['pattern'][$name]
              ?? config('route.default_pattern_regex', '\w+');
          }
        }
      }
      if (!str_starts_with($path, '/')) {
        $path = "/$path";
      } else {
        $path = $path === '/' ? '/' : rtrim(!$case ? strtolower($path) : $path, '/');
      }
    }
    $this->pattern($pattern);
    // 合并父级path
    if (!empty($this->options['paths'])) {
      $mergePaths = [];
      foreach ($this->options['paths'] as $path1) {
        foreach ($paths as $path2) {
          if ($path2 === '/') {
            $mergePaths[] = $path1;
          } else {
            $path1 = $path1 === '/' ? '' : $path1;
            $mergePaths[] = $path1 . $path2;
          }
        }
      }
      $this->options['paths'] = $mergePaths;
    } else {
      $this->options['paths'] = $paths;
    }
  }

  /**
   * 变量规则
   * @param array $pattern ['name'=>pattern]
   * @return RouteAbstract
   */
  public function pattern(array $pattern): static
  {
    $this->options['pattern'] = $pattern;
    return $this;
  }

  /**
   * 路由处理
   *
   * @param callable|string|array $handler
   * @return void
   */
  protected function handler(callable|string|array $handler): void
  {
    if (is_string($handler) && str_contains($handler, '@')) {
      $handler = explode('@', $handler);
    }
    if (!is_array($handler) && !is_callable($handler)) {
      throw new InvalidArgumentException(
        '路由handler配置错误，需给定class::method|class@method|[class|object,method]|Closure'
      );
    }
    // [类=>方法] | 闭包
    $this->options['handler'] = $handler;
  }

  /**
   * 请求方法
   *
   * @param string|array[] $method
   * @return static
   */
  public function method(string|array $method = '*'): static
  {
    if (is_array($method)) {
      $this->options['method'] = array_map('strtoupper', $method);
    } else {
      $this->options['method'] = explode(',', strtoupper($method));
    }
    return $this;
  }

  /**
   * 获取配置
   *
   * @return array{
   *   paths: string[],
   *   describe: string,
   *   handler: callable,
   *   method: string[],
   *   params: array,
   *   middleware: array,
   *   suffix: string[],
   *   domain: string[],
   *   hidden: bool,
   * }
   */
  public function getOptions(): array
  {
    return $this->options;
  }

  /**
   * 路由描述
   *
   * @param string $describe 描述
   * @return static
   */
  public function describe(string $describe): static
  {
    $this->options['describe'] = $describe;
    return $this;
  }

  /**
   * 设置路由中间件
   * @access public
   * @param string|Closure|array{string|Closure} $middleware middleware::class | Closure
   * @return static
   */
  public function middleware(string|Closure|array $middleware): static
  {
    if (!is_array($middleware)) {
      $middleware = [$middleware];
    }
    $this->options['middleware'] = array_merge($this->options['middleware'], $middleware);
    return $this;
  }

  /**
   * 注册路由
   *
   * @param RouteCollector $collector 当前路线收集器实例
   * @return void
   */
  abstract public function register(RouteCollector $collector): void;

  /**
   * 请求参数校验规则
   *
   * @param array $params 参数校验规则
   * @return static
   */
  public function params(array $params): static
  {
    $this->options['params'] = array_merge($this->options['params'], $params);
    return $this;
  }

  /**
   * 批量设置选项
   *
   * @param array $options
   * @return RouteAbstract
   */
  public function options(array $options): static
  {
    foreach ($options as $key => $value) {
      if (method_exists(__CLASS__, $key)) {
        $this->$key($value);
      } else {
        trigger_error("不存在{$key}路由选项", E_USER_WARNING);
      }
    }
    return $this;
  }

  /**
   * @inheritDoc
   */
  #[Override] public function offsetExists(mixed $offset): bool
  {
    return array_key_exists($offset, $this->options);
  }

  /**
   * @inheritDoc
   */
  #[Override] public function offsetGet(mixed $offset): mixed
  {
    return $this->options[$offset] ?? null;
  }

  /**
   * @inheritDoc
   */
  #[Override] public function offsetSet(mixed $offset, mixed $value): void
  {
    throw new RuntimeException('Router option is read-only.');
  }

  /**
   * @inheritDoc
   */
  #[Override] public function offsetUnset(mixed $offset): void
  {
    throw new RuntimeException('Router option is read-only.');
  }
}
