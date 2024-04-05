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
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use Swoole\Http\Request as swooleRequest;
use ViSwoole\Core\Contract\RequestInterface;
use ViSwoole\Core\Facade;

/**
 * HTTP请求对象
 *
 * @method static RequestInterface __make() 获取当前实例
 * @method static mixed get(?string $key, mixed $default) 获取get参数
 * @method static array getServerParams() 检索服务器参数。
 * @method static array getCookieParams() 检索 Cookie。
 * @method static ServerRequestInterface withCookieParams(array $cookies) 返回具有指定 Cookie 的实例。
 * @method static RequestInterface create(?swooleRequest $request) 创建request对象
 * @method static array getQueryParams() 检索查询字符串参数。
 * @method static ServerRequestInterface withQueryParams(array $query) 返回具有指定查询字符串参数的实例。
 * @method static array getUploadedFiles() 检索标准化的文件上传数据。
 * @method static ServerRequestInterface withUploadedFiles(array $uploadedFiles) 创建具有指定上传文件的新实例。
 * @method static object|array|null getParsedBody() 检索请求体中提供的参数。
 * @method static ServerRequestInterface withParsedBody(mixed $data) 返回具有指定 body 参数的实例。
 * @method static mixed getAttribute(string $name, mixed $default) 检索单个派生的请求属性。
 * @method static array getAttributes() 检索从请求派生的属性。
 * @method static ServerRequestInterface withAttribute(string $name, mixed $value) 返回具有指定派生请求属性的实例。
 * @method static ServerRequestInterface withoutAttribute(string $name) 返回删除指定派生请求属性的实例。
 * @method static array|null getBasicAuthCredentials() 获取基本身份验证票据
 * @method static array getHeader(string $name) 通过给定的不区分大小写的名称检索消息头值。
 * @method static string getRequestTarget() 获取消息的请求目标。
 * @method static RequestInterface withRequestTarget(string $requestTarget) 返回具有指定请求目标的实例。
 * @method static RequestInterface withMethod(string $method) 返回具有提供的 HTTP 方法的实例。
 * @method static UriInterface getUri() 检索 URI 实例。
 * @method static RequestInterface withUri(UriInterface $uri, bool $preserveHost) 返回具有提供的 URI 的实例。
 * @method static string getProtocolVersion() 获取HTTP协议版本。
 * @method static RequestInterface withProtocolVersion(string $version) 返回指定的HTTP协议版本的新实例。
 * @method static bool hasHeader(string $name) 通过给定的不区分大小写的名称检查标头是否存在。
 * @method static mixed header(string $name, string $default = null) 获取请求头。
 * @method static array getHeaderLines(string $formatTheHeader = 'lower') 检索所有消息标头的值的逗号分隔字符串。
 * @method static array getHeaders(string $formatTheHeader = 'lower') 检索所有消息头的值。
 * @method static RequestInterface withHeader(string $name, mixed $value) 使用提供的值替换指定标头的实例。
 * @method static RequestInterface withAddedHeader(string $name, mixed $value) 返回附加了给定值的指定标头的实例。
 * @method static RequestInterface withoutHeader(string $name) 返回没有指定标头的实例。
 * @method static StreamInterface getBody() 获取消息的主体。
 * @method static RequestInterface withBody(StreamInterface $body) 返回具有指定消息主体的实例。
 * @method static string ip() 获取客户端ip
 * @method static array getParams(array|string|null $rule, bool $isShowNull) 批量获取请求参数
 * @method static mixed param(?string $key, mixed $default, ?string $filter) 获取请求参数
 * @method static string getMethod() 检索请求的 HTTP 方法。
 * @method static mixed post(?string $key, mixed $default) 获取post参数
 * @method static string filter(string $data, array|string|null $filter) 过滤
 * @method static bool isJson() 当前是否JSON请求
 * @method static string getHeaderLine(string $name) 检索单个标头的值的逗号分隔字符串。
 * @method static string getAcceptType() 当前请求的资源类型
 * @method static swooleRequest getSwooleRequest() 获取swoole请求对象
 */
class Request extends Facade
{

  protected static bool $alwaysNewInstance = true;

  /**
   * 获取当前Facade对应类名
   *
   * @access protected
   * @return string
   */
  #[Override] protected static function getFacadeClass(): string
  {
    return \ViSwoole\Core\Server\Http\Request::class;
  }
}
