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

namespace ViSwoole\Core\Router\Annotation;

use Attribute;
use ViSwoole\HttpServer\Method;

/**
 * Controller注解
 */
#[Attribute(Attribute::TARGET_CLASS)]
class RouteController extends AnnotationRouteAbstract
{
  /**
   * @param array|string|null $prefix 前缀，null代表当前控制器类名称
   * @param array|Method $methods 请求方法
   * @param string|null $server 服务器名称
   * @param array $options 更多配置选项
   */
  public function __construct(
    array|string|null $prefix = null,
    array|Method      $methods = [Method::GET, Method::POST],
    public ?string    $server = null,
    array             $options = []
  )
  {
    parent::__construct($prefix, $methods, $options);
  }
}
