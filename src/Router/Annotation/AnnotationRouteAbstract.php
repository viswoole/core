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


abstract class AnnotationRouteAbstract
{
  /**
   * @param string|string[]|null $paths null则为当前方法名
   * @param string|array $methods
   * @param array{describe:string,params:array,middleware:array,suffix:string[],domain:string[],pattern:array} $options
   */
  public function __construct(
    public string|array|null $paths = null,
    public string|array      $methods = ['GET', 'POST'],
    public array             $options = []
  )
  {
    $this->options['method'] = $this->methods;
  }
}
