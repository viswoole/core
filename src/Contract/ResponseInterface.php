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

use Swoole\Http\Response as swooleResponse;

interface ResponseInterface extends \Psr\Http\Message\ResponseInterface
{
  /**
   * 重定向
   *
   * @param string $uri
   * @param int $http_code 302|301
   * @return bool
   */
  public function redirect(string $uri, int $http_code = 302): bool;

  /**
   * 获取swoole的Response对象
   *
   * @access public
   * @return swooleResponse
   */
  public function getSwooleResponse(): swooleResponse;

  /**
   * 发送响应
   *
   * @access public
   * @param string|null $content
   * @return bool
   */
  public function send(?string $content = null): bool;

  /**
   * 输出内容类型
   *
   * @access public
   * @param string $contentType 输出类型
   * @param string $charset 输出编码
   * @return static
   */
  public function setContentType(string $contentType = 'application/json', string $charset = 'utf-8'
  ): ResponseInterface;

  /**
   * 发送HTTP状态
   *
   * @access public
   * @param int $statusCode 状态码
   * @param string $reasonPhrase 状态描述短语
   * @return static
   */
  public function setCode(int $statusCode, string $reasonPhrase = ''): ResponseInterface;

  /**
   * 设置响应头(可批量设置)
   *
   * @access public
   * @param string|array $name 不区分大小写标头或[$name=>$value]
   * @param array|string|null $value 标头值
   * @return static
   */
  public function setHeader(string|array $name, array|string|null $value = null): ResponseInterface;

  /**
   * 标准错误响应格式
   *
   * @access public
   * @param string $errMsg 错误提示信息
   * @param int $errCode 错误码
   * @param array|null $data 响应数据
   * @return static
   */
  public function error(
    string $errMsg = 'error',
    int    $errCode = -1,
    array  $data = null
  ): ResponseInterface;

  /**
   * 标准系统内部错误响应
   *
   * @param string $errMsg 错误提示信息
   * @param int $errCode 错误码
   * @param int $statusCode 状态码
   * @param array|null $errTrace 额外数据
   * @return static
   */
  public function exception(
    string $errMsg = '系统内部错误',
    int    $errCode = 500,
    int    $statusCode = 500,
    array  $errTrace = null
  ): ResponseInterface;

  /**
   * 标准json响应
   *
   * @access public
   * @param array|object $data
   * @param int $statusCode
   * @return static
   */
  public function json(array|object $data, int $statusCode = 200): ResponseInterface;

  /**
   * 标准成功响应格式
   *
   * @access public
   * @param string|array $errMsg 提示信息,如果传入数组则做为响应数据默认提示信息为success
   * @param array|null $data 响应数据
   * @return ResponseInterface
   */
  public function success(string|array $errMsg = 'success', array $data = null): ResponseInterface;

  /**
   * 检索所有消息头的值。
   *
   * 该方法返回所有标头和值的字符串，这些值使用逗号拼接在一起。
   *
   * @return array 所有标头。
   */
  public function getHeaderLines(): array;

  /**
   * 设置响应内容
   *
   * @access public
   * @param string $content
   * @return static
   */
  public function setContent(string $content): ResponseInterface;

  /**
   * 设置响应内容
   *
   * @access public
   * @param string $content
   * @return ResponseInterface
   */
  public function setMessage(string $content): ResponseInterface;

  /**
   * 是否将响应回显到控制台
   *
   * @param bool $echo
   * @return ResponseInterface
   */
  public function echoConsole(bool $echo = true): ResponseInterface;

  /**
   * 发送文件
   *
   * @param string $filePath 要发送的文件名称
   * @param int $offset 上传文件的偏移量
   * @param int $length 发送数据的尺寸
   * @param string|null $fileMimeType 文件类型
   * @return bool
   */
  public function sendfile(
    string $filePath, int $offset = 0, int $length = 0, ?string $fileMimeType = null
  ): bool;

  /**
   * rawCookie() 的参数和上文的 setCookie() 一致，只不过不进行编码处理
   *
   * @access public
   * @param string $key
   * @param string $value
   * @param int $expire
   * @param string $path
   * @param string $domain
   * @param bool $secure
   * @param bool $httponly
   * @param string $samesite
   * @param string $priority
   * @return bool
   * @see static::setCookie()
   */
  public function rawCookie(
    string $key,
    string $value = '',
    int    $expire = 0,
    string $path = '/',
    string $domain = '',
    bool   $secure = false,
    bool   $httponly = false,
    string $samesite = '',
    string $priority = ''
  ): bool;

  /**
   * 设置Cookie信息
   *
   * @access public
   * @param string $key
   * @param string $value
   * @param int $expire 过期时间
   * @param string $path 存储路径
   * @param string $domain 域名
   * @param bool $secure 是否通过安全的 HTTPS 连接来传输 Cookie
   * @param bool $httponly 是否允许浏览器的JavaScript访问带有 HttpOnly 属性的 Cookie
   * @param string $samesite 限制第三方 Cookie，从而减少安全风险
   * @param string $priority Cookie优先级，当Cookie数量超过规定，低优先级的会先被删除
   * @return bool
   */
  public function setCookie(
    string $key,
    string $value = '',
    int    $expire = 0,
    string $path = '/',
    string $domain = '',
    bool   $secure = false,
    bool   $httponly = false,
    string $samesite = '',
    string $priority = ''
  ): bool;
}
