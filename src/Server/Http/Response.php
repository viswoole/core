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

namespace ViSwoole\Core\Server\Http;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Swoole\Http\Response as swooleResponse;
use ViSwoole\Core\Console\Output;
use ViSwoole\Core\Contract\ResponseInterface;
use ViSwoole\Core\Coroutine;
use ViSwoole\Core\Coroutine\Context;
use ViSwoole\Core\Server\Http\Message\FileStream;

class Response implements ResponseInterface
{
  /**
   * @var int 响应状态码
   */
  protected int $statusCode = Status::OK;
  /**
   * @var string 协议版本
   */
  protected string $protocolVersion = '1.1';
  /**
   * @var string 状态描述短语
   */
  protected string $reasonPhrase = 'OK';
  /**
   * @var array 响应标头
   */
  protected array $headers = [
    'Access-Control-Allow-Origin' => '*',
    'Access-Control-Allow-Headers' => 'Authorization, Timestamp ,Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-Requested-With',
    'Access-Control-Allow-Methods' => 'GET,POST,PATCH,PUT,DELETE,OPTIONS,DELETE',
  ];
  /**
   * @var int json_encode flags 参数
   */
  protected int $jsonFlags = JSON_UNESCAPED_UNICODE;
  /**
   * @var bool 是否把消息输出到控制台，建议在调试阶段使用
   */
  protected bool $messageEchoToConsole = false;
  /**
   * @var StreamInterface body流
   */
  private StreamInterface $stream;
  /**
   * @var swooleResponse swoole响应对象
   */
  private swooleResponse $swooleResponse;

  public function __construct(swooleResponse $response)
  {
    $this->swooleResponse = $response;
  }

  /**
   * 自定义实例化
   *
   * @return static
   */
  public static function __make(): static
  {
    return Context::get(__CLASS__, Coroutine::getTopId());
  }

  /**
   * 检索 HTTP 协议版本号作为字符串。
   *
   * @return string HTTP 版本号（例如，"1.1"，"1.0"）。
   */
  public function getProtocolVersion(): string
  {
    return $this->protocolVersion;
  }

  /**
   * 返回具有指定的 HTTP 协议版本的实例。
   *
   * @param string $version HTTP 版本号（例如，"1.1"，"1.0"）。
   * @return static
   */
  public function withProtocolVersion(string $version): ResponseInterface
  {
    $newResponse = clone $this;
    $newResponse->protocolVersion = $version;
    return $newResponse;
  }

  /**
   * 通过给定不区分大小写的名称检索标头的值，这些值以逗号分隔的字符串形式返回。
   *
   * 该方法返回给定不区分大小写的标头名称的所有标头值的字符串，这些值使用逗号拼接在一起。
   *
   * 注意：并非所有标头值都可以使用逗号拼接来适当表示。对于这样的标头，请改用 getHeader()，
   * 并在拼接时提供自己的分隔符。
   *
   * 如果消息中不包含标头，则此方法必须返回一个空字符串。
   *
   * @param string $name 不区分大小写的标头字段名称。
   * @return string 作为给定标头的所有字符串值的逗号拼接字符串。
   * 如果消息中没有该标头，则此方法必须返回一个空字符串。
   */
  public function getHeaderLine(string $name): string
  {
    return Header::getHeader($name, $this->headers, 'string');
  }

  /**
   * 通过给定不区分大小写的名称检索消息标头值。
   *
   * 该方法返回给定不区分大小写的标头名称的所有标头值的数组。
   *
   * 如果消息中不包含标头，则此方法必须返回一个空数组。
   *
   * @param string $name 不区分大小写的标头字段名称。
   * @return string[] 作为给定标头的所有字符串值的数组。如果消息中没有该标头，则此方法必须返回一个空数组。
   */
  public function getHeader(string $name): array
  {
    return Header::getHeader($name, $this->headers);
  }

  /**
   * 检索所有消息头的值。
   *
   * 该方法返回所有标头和值的字符串，这些值使用逗号拼接在一起。
   *
   * @access public
   * @return array 所有标头。
   */
  public function getHeaderLines(): array
  {
    return Header::getHeaders($this->headers, 'string', 'title');
  }

  /**
   * 检索所有消息头的值。
   *
   * @return string[][] 返回消息标头的关联数组。
   */
  public function getHeaders(): array
  {
    return Header::getHeaders($this->headers, 'array', 'title');
  }

  /**
   * 返回具有指定值附加到给定值的标头的实例。
   *
   * 将保留指定标头的现有值。新值将附加到现有列表中。如果标头以前不存在，则将其添加。
   *
   * @param string $name 不区分大小写的标头字段名称。
   * @param string|string[] $value 标头值。
   * @return static
   * @throws InvalidArgumentException 对于无效的标头名称或值。
   */
  public function withAddedHeader(string $name, $value): ResponseInterface
  {
    Header::validate($name, $value);
    $newRequest = clone $this;
    $newName = Header::hasHeader($name, $newRequest->headers);
    if (is_bool($newName)) {
      $newName = Header::formatName($name);
    }
    if (is_array($newRequest->headers[$newName])) {
      if (is_string($value)) $value = explode(',', $value);
      $newRequest->headers[$newName] = array_merge($newRequest->headers[$newName], $value);
    } else {
      if (is_array($value)) $value = implode(',', $value);
      $newRequest->headers[$newName] .= $value;
    }
    return $newRequest;
  }

  /**
   * 检查是否存在给定不区分大小写名称的标头。
   *
   * @param string $name 不区分大小写的标头字段名称。
   * @return bool 如果任何标头名称与给定的标头名称使用不区分大小写的字符串比较匹配，则返回 true。
   * 如果消息中没有找到匹配的标头名称，则返回 false。
   */
  public function hasHeader(string $name): bool
  {
    $lowercaseArray = array_change_key_case($this->headers);
    return array_key_exists(strtolower($name), $lowercaseArray);
  }

  /**
   * 返回一个没有指定标头的实例。
   *
   * 标头解析必须在不区分大小写的情况下进行。
   *
   * 此方法必须以保持消息的不可变性的方式实现，并且必须返回移除命名标头的实例。
   *
   * @param string $name 不区分大小写的标头字段名称要删除。
   * @return static
   */
  public function withoutHeader(string $name): ResponseInterface
  {
    $name = Header::hasHeader($name, $this->headers);
    if (false === $name) {
      return $this;
    } else {
      $newRequest = clone $this;
      unset($newRequest->headers[$name]);
      return $newRequest;
    }
  }

  /**
   * 返回具有指定消息主体的实例。
   *
   * 主体必须是一个 StreamInterface 对象。
   *
   * 此方法必须以保持消息的不可变性的方式实现，并且必须返回具有新主体流的新实例。
   *
   * @param StreamInterface $body 主体。
   * @return static
   * @throws InvalidArgumentException 当主体无效时。
   */
  public function withBody(StreamInterface $body): ResponseInterface
  {
    $newRequest = clone $this;
    $newRequest->stream = $body;
    return $newRequest;
  }

  /**
   * 获取响应状态代码。
   *
   * 状态代码是服务器尝试理解和满足请求的结果代码，是一个 3 位整数。
   *
   * @return int 状态代码。
   */
  public function getStatusCode(): int
  {
    return $this->statusCode;
  }

  /**
   * 重定向
   *
   * @param string $uri
   * @param int $http_code 302|301
   * @return bool
   */
  public function redirect(string $uri, int $http_code = 302): bool
  {
    return $this->getResponse()->redirect($uri, $http_code);
  }

  /**
   * 获取swoole的Response对象
   *
   * @access public
   * @return swooleResponse
   */
  public function getResponse(): swooleResponse
  {
    return $this->swooleResponse;
  }

  /**
   * 发送响应
   *
   * @access public
   * @param string|null $content
   * @return bool
   */
  public function send(?string $content = null): bool
  {
    if ($this->getResponse()->isWritable()) {
      foreach ($this->headers as $k => $v) {
        $this->getResponse()->setHeader($k, $v);
      }
      $this->getResponse()->setStatusCode($this->statusCode);
      if ($content === null) {
        $content = $this->getBody()->getContents();
      }
      if ($this->messageEchoToConsole) {
        $fd = $this->getResponse()->fd;
        // 获得请求进入时间
        $request_time_float = \ViSwoole\Core\Server\Http\Facades\Request::getSwooleRequest()
          ->server['request_time_float'];
        // 获取当前时间
        $current_time_float = microtime(true);
        // 计算耗时
        $elapsed_time = $current_time_float - $request_time_float;
        // 输出到控制台
        Output::dump($content, "($fd)响应内容:耗时{$elapsed_time}秒");
      }
      return $this->getResponse()->end($content);
    } else {
      return false;
    }
  }

  /**
   * 设置响应头(可批量设置) 影响当前响应对象
   *
   * @access public
   * @param string|array $name 不区分大小写标头或[$name=>$value]
   * @param array|string|null $value 标头值
   * @return static
   */
  public function setHeader(array|string $name, array|string|null $value = null): static
  {
    $newResponse = $this;
    if (is_array($name)) {
      foreach ($name as $headerName => $headerValue) {
        Header::validate($headerName, $headerValue);
        $newName = Header::hasHeader($headerName, $newResponse->headers);
        if (is_bool($newName)) $newName = Header::formatName($headerName);
        $newResponse->headers[$newName] = is_array($headerValue) ? implode(
          ',', $headerValue
        ) : $headerValue;
      }
    } else {
      if (empty($value)) throw new InvalidArgumentException('响应标头值不可为空');
      Header::validate($name, $value);
      $newName = Header::hasHeader($name, $newResponse->headers);
      if (is_bool($newName)) $newName = Header::formatName($name);
      $newResponse->headers[$newName] = is_array($value) ? implode(',', $value) : $value;
    }
    return $newResponse;
  }

  /**
   * 获取消息的主体。
   *
   * @return StreamInterface 以流形式返回主体。
   */
  public function getBody(): StreamInterface
  {
    if (!isset($this->stream)) {
      $this->stream = FileStream::create('php://memory', 'r+');
    }
    return $this->stream;
  }

  /**
   * 创建响应对象
   *
   * @param swooleResponse|null $response
   * @return static
   */
  public static function create(?swooleResponse $response = null): static
  {
    $instance = Context::get(__CLASS__, null, Coroutine::getTopId());
    if (is_null($instance)) {
      if (is_null($response)) $response = swooleResponse::create();
      $requestClass = Response::class;
      if (class_exists('\App\Response')) $requestClass = \App\Response::class;
      $instance = new $requestClass($response);
      Context::set(__CLASS__, $instance, Coroutine::getTopId());
    }
    return $instance;
  }

  /**
   * 设置Content-Type响应头
   *
   * @access public
   * @param string $contentType 输出类型 默认application/json
   * @param string $charset 输出编码 默认utf-8
   * @return static
   */
  public function contentType(string $contentType = 'application/json', string $charset = 'utf-8'
  ): static
  {
    return $this->withHeader('Content-Type', "$contentType; charset=$charset");
  }

  /**
   * 返回一个具有指定值，替换指定标头的实例。
   *
   * 虽然标头名称不区分大小写，但此函数会保留标头的大小写，并从 getHeaders() 返回。
   *
   * 此方法必须以保持消息的不可变性的方式实现，并且必须返回具有新标头和/或值的实例。
   *
   * @param string $name 不区分大小写的标头字段名称，自动格式化为合法标头。
   * @param string|string[] $value 标头值（们）。
   * @return static
   * @throws InvalidArgumentException 对于无效的标头名称或值。
   */
  public function withHeader(string $name, $value): ResponseInterface
  {
    Header::validate($name, $value);
    $newResponse = clone $this;
    $newName = Header::hasHeader($name, $newResponse->headers);
    if (is_bool($newName)) $newName = Header::formatName($name);
    $newResponse->headers[$newName] = is_array($value) ? implode(',', $value) : $value;
    return $newResponse;
  }

  /**
   * 标准错误响应格式
   *
   * @access public
   * @param mixed $errMsg 提示信息array|object为data
   * @param int $errCode 业务错误码
   * @param mixed $data 响应数据
   * @return static
   */
  public function error(mixed $errMsg = 'error', int $errCode = -1, mixed $data = null): static
  {
    if (is_array($errMsg) || is_object($errMsg) || is_null($errMsg)) {
      $data = $data ?: $errMsg;
      $errMsg = 'error';
    }
    $res = [
      'errCode' => $errCode,
      'errMsg' => $errMsg,
      'data' => $data
    ];
    return $this->json($res);
  }

  /**
   * json响应
   *
   * @access public
   * @param array|object $data
   * @param int $statusCode
   * @return static
   */
  public function json(object|array $data, int $statusCode = 200): static
  {
    $newResponse = clone $this;
    $newResponse->headers['Content-Type'] = 'application/json; charset=utf-8';
    $newResponse->setContent(json_encode($data, $this->jsonFlags));
    $newResponse->statusCode = $statusCode;
    return $newResponse;
  }

  /**
   * 设置响应内容
   *
   * @access public
   * @param string $content
   * @return static
   */
  public function setContent(string $content): static
  {
    $this->stream = FileStream::create('php://memory', 'r+');
    $this->stream->write($content);
    return $this;
  }

  /**
   * 设置响应内容
   *
   * @access public
   * @param string $content
   * @return static
   */
  public function setMessage(string $content): static
  {
    return $this->setContent($content);
  }

  /**
   * 是否将响应回显到控制台
   *
   * @param bool $echo
   * @return $this
   */
  public function echoConsole(bool $echo = true): static
  {
    $this->messageEchoToConsole = $echo;
    return $this;
  }

  /**
   * 标准系统内部错误响应
   *
   * @param string $errMsg 错误提示信息
   * @param int $errCode 业务错误码
   * @param int $statusCode http状态码
   * @param mixed $data 数据
   * @return static
   */
  public function exception(
    string $errMsg = '系统内部错误', int $errCode = 500, int $statusCode = 500, mixed $data = null
  ): static
  {
    $res = [
      'errCode' => $errCode,
      'errMsg' => $errMsg,
      'data' => $data
    ];
    return $this->json($res, $statusCode);
  }

  /**
   * 标准成功响应格式
   *
   * @access public
   * @param mixed $errMsg 提示信息array|object为data
   * @param mixed $data 响应数据
   * @return static
   */
  public function success(mixed $errMsg = 'success', mixed $data = null): static
  {
    if (is_array($errMsg) || is_object($errMsg) || is_null($errMsg)) {
      $data = $data ?? $errMsg;
      $errMsg = 'success';
    }
    $res = [
      'errCode' => 0,
      'errMsg' => (string)$errMsg,
      'data' => $data
    ];
    return $this->json($res);
  }

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
  ): bool
  {
    if (is_null($fileMimeType)) {
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $fileMimeType = finfo_file($finfo, $filePath);
      finfo_close($finfo);
    }
    $this->swooleResponse->header('Content-Type', $fileMimeType);
    return $this->swooleResponse->sendfile($filePath);
  }

  /**
   * setHeader别名方法
   *
   * @access public
   * @param string|array $name 不区分大小写标头或[$name=>$value]
   * @param array|string|null $value 标头值
   * @return static
   */
  public function header(array|string $name, array|string|null $value = null): ResponseInterface
  {
    return $this->setHeader($name, $value);
  }

  /**
   * 发送HTTP状态
   *
   * @access public
   * @param int $statusCode 状态码
   * @param string $reasonPhrase 状态描述短语
   * @return static
   */
  public function code(int $statusCode, string $reasonPhrase = ''): static
  {
    return $this->setCode($statusCode, $reasonPhrase);
  }

  /**
   * 发送HTTP状态
   *
   * @access public
   * @param int $statusCode 状态码
   * @param string $reasonPhrase 状态描述短语
   * @return static
   */
  public function setCode(int $statusCode, string $reasonPhrase = ''): ResponseInterface
  {
    return $this->withStatus($statusCode, $reasonPhrase);
  }

  /**
   * 返回具有指定状态代码和（可选）原因短语的实例。
   *
   * 如果没有指定原因短语，实现可以选择将响应状态代码的 RFC 7231 或 IANA 推荐原因短语作为默认值。
   *
   * @param int $code 要设置的 3 位整数结果代码。
   * @param string $reasonPhrase 与提供的状态代码一起使用的原因短语；如果未提供，则实现可以使用 HTTP 规范中建议的默认值。
   * @return static
   * @throws InvalidArgumentException 对于无效的状态代码参数。
   */
  public function withStatus(int $code, string $reasonPhrase = ''): static
  {
    // 检查状态码是否有效
    if ($code < 100 || $code >= 600) {
      throw new InvalidArgumentException(
        'Invalid HTTP status code, correct value should be between 100 and 599 '
      );
    }
    $newRequest = clone $this;
    $newRequest->statusCode = $code;
    if (empty($reasonPhrase)) {
      $reasonPhrase = Status::getReasonPhrase($code);
    }
    $newRequest->reasonPhrase = $reasonPhrase;
    return $newRequest;
  }

  /**
   * 获取与状态代码相关联的响应原因短语。
   *
   * 由于响应状态行中的原因短语不是必需元素，原因短语值可以为 null。实现可以选择返回响应状态代码的 RFC 7231 推荐原因短语
   * （或 IANA HTTP 状态码注册中的原因短语列表）作为默认值。
   *
   * @return string 原因短语；如果没有则必须返回一个空字符串。
   */
  public function getReasonPhrase(): string
  {
    return $this->reasonPhrase;
  }
}
