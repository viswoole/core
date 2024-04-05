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

namespace ViSwoole\Core\Server\Http\Facades;

use Override;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use ViSwoole\Core\Facade;

/**
 * HTTP响应类
 *
 * @method static \ViSwoole\Core\Server\Http\Response __make() 返回Response单例（每个请求都会是独立的request）
 * @method static string getProtocolVersion() 检索 HTTP 协议版本号作为字符串。
 * @method static \ViSwoole\Core\Server\Http\Response withProtocolVersion(string $version) 返回具有指定的 HTTP 协议版本的实例。
 * @method static string getHeaderLine(string $name) 通过给定不区分大小写的名称检索标头的值，这些值以逗号分隔的字符串形式返回。
 * @method static array getHeader(string $name) 通过给定不区分大小写的名称检索消息标头值。
 * @method static array getHeaderLines() 检索所有消息头的值。
 * @method static array getHeaders() 检索所有消息头的值。
 * @method static MessageInterface withAddedHeader(string $name, mixed $value) 返回具有指定值附加到给定值的标头的实例。
 * @method static bool hasHeader(string $name) 检查是否存在给定不区分大小写名称的标头。
 * @method static MessageInterface withoutHeader(string $name) 返回一个没有指定标头的实例。
 * @method static MessageInterface withBody(StreamInterface $body) 修改响应的消息主体。
 * @method static int getStatusCode() 获取响应状态代码。
 * @method static bool redirect(string $uri, int $http_code) 重定向
 * @method static \Swoole\Http\Response getSwooleResponse() 获取swoole的Response对象
 * @method static bool send(mixed $content) 发送响应
 * @method static StreamInterface getBody() 获取消息的主体。
 * @method static \ViSwoole\Core\Server\Http\Response create(\Swoole\Http\Response $response) 创建响应对象
 * @method static \ViSwoole\Core\Server\Http\Response setContentType(string $contentType, string $charset) 设置Content-Type响应头
 * @method static MessageInterface withHeader(string $name, mixed $value) 修改响应头。
 * @method static \ViSwoole\Core\Server\Http\Response withStatus(int $code, string $reasonPhrase) 修改状态码
 * @method static string getReasonPhrase() 获取与状态代码相关联的响应原因短语。
 * @method static \ViSwoole\Core\Server\Http\Response error(string|array $errMsg, array $data = null) 标准错误响应格式
 * @method static \ViSwoole\Core\Server\Http\Response json(object|array $data, int $statusCode) 标准json响应
 * @method static \ViSwoole\Core\Server\Http\Response setContent(string $content) 设置响应内容
 * @method static \ViSwoole\Core\Server\Http\Response exception(string $errMsg, int $errCode = 500, int $statusCode = 500, array $data = null) 标准系统内部错误响应
 * @method static \ViSwoole\Core\Server\Http\Response success(string|array $errMsg, array $data = null) 标准成功响应格式
 * 优化命令：php visual optimize:facade \\ViSwoole\\Core\\Server\\Http\\Response
 */
class Response extends Facade
{

  /**
   * 获取当前Facade对应类名
   *
   * @access protected
   * @return string
   */
  #[Override] protected static function getFacadeClass(): string
  {
    return \ViSwoole\Core\Server\Http\Response::class;
  }
}
