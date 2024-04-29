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

use ViSwoole\HttpServer\Method;

abstract class AnnotationRouteAbstract
{
  /**
   * @param string|string[]|null $paths null则为当前方法名
   * @param Method|array $methods
   * @param string|null $server 服务器
   * @param array $options
   */
  public function __construct(
    public string|array|null $paths = null,
    public Method|array      $methods = [Method::GET, Method::POST],
    public array             $options = []
  )
  {
    $this->options['method'] = $this->methods;
  }
}
