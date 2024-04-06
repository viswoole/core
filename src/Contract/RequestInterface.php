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

namespace ViSwoole\Core\Contract;

use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Request as swooleRequest;

interface RequestInterface extends ServerRequestInterface
{
  /**
   * 创建request对象
   *
   * @access public
   * @param swooleRequest|null $request
   * @return static
   */
  public static function create(?swooleRequest $request = null): RequestInterface;

  /**
   * 获取客户端ip
   * @return string
   */
  public function ip(): string;

  /**
   * 批量获取请求参数
   * @access public
   * @param string|array|null $rule 可传key或[key=>default,...]或[key1,key2....]
   * @param bool $isShowNull 是否显示为null的字段
   * @return array
   */
  public function getParams(string|array|null $rule = null, bool $isShowNull = true): array;

  /**
   * 获取请求参数
   * @param string|null $key
   * @param mixed $default
   * @param string|null $filter
   * @return mixed
   */
  public function param(?string $key = null, mixed $default = null, string $filter = null): mixed;

  /**
   * 获取post参数
   * @param string|null $key 要获取的参数
   * @param mixed|null $default 默认值
   * @return mixed
   */
  public function post(?string $key = null, mixed $default = null): mixed;

  /**
   * 获取get参数
   * @param string|null $key 要获取的参数
   * @param mixed|null $default 默认值
   * @return mixed
   */
  public function get(?string $key = null, mixed $default = null): mixed;

  /**
   * 过滤
   *
   * @param string $data
   * @param array|string|null $filter 传入null则使用全局过滤方法进行过滤
   * @return string
   */
  public function filter(string $data, array|string|null $filter = null): string;

  /**
   * 当前是否JSON请求
   * @access public
   * @return bool
   */
  public function isJson(): bool;

  /**
   * 当前请求的资源类型
   * @access public
   * @return string
   */
  public function getAcceptType(): string;

  /**
   * 获取基本身份验证票据
   * @return array|null AssociativeArray(username,password)
   */
  public function getBasicAuthCredentials(): ?array;

  /**
   * 检索所有消息头的值。
   *
   * 该方法返回所有标头和值的字符串，这些值使用逗号拼接在一起。
   *
   * @param string $formatTheHeader lower|upper|title
   * @return array 所有标头。
   */
  public function getHeaderLines(string $formatTheHeader = 'lower'): array;

  /**
   * 判断是否https访问
   *
   * @return bool
   */
  public function https(): bool;

  /**
   * 获取访问资源路径
   *
   * @return string
   */
  public function getPath(): string;

  /**
   * 获取swooleRequest
   *
   * @return swooleRequest
   */
  public function getSwooleRequest(): swooleRequest;

  /**
   * 添加请求参数
   *
   * @param array $params
   * @return void
   */
  public function addParams(array $params): void;

  /**
   * 获取请求标头
   *
   * @param string $name
   * @param string|null $default
   * @return mixed
   */
  public function header(string $name, string $default = null): mixed;
}
