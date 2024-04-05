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
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;
use Swoole\Http\Request as swooleRequest;
use ViSwoole\Core\Contract\RequestInterface;
use ViSwoole\Core\Coroutine;
use ViSwoole\Core\Coroutine\Context;
use ViSwoole\Core\Facades\Server;
use ViSwoole\Core\Server\Http\Message\FileStream;
use ViSwoole\Core\Server\Http\Message\Uri;

/**
 * 该类用于Swoole\Http\Request类进行代理封装
 */
class Request implements RequestInterface
{
  /**
   * 请求类型
   */
  public const array ACCEPT_TYPE = [
    'xml' => 'application/xml,text/xml,application/x-xml',
    'json' => 'application/json,text/x-json,text/json',
    'js' => 'text/javascript,application/javascript,application/x-javascript',
    'css' => 'text/css',
    'rss' => 'application/rss+xml',
    'yaml' => 'application/x-yaml,text/yaml',
    'atom' => 'application/atom+xml',
    'pdf' => 'application/pdf',
    'text' => 'text/plain',
    'image' => 'image/png,image/jpg,image/jpeg,image/pjpeg,image/gif,image/webp,image/*',
    'csv' => 'text/csv',
    'html' => 'text/html,application/xhtml+xml,*/*',
  ];
  /**
   *  例1 ['htmlspecialchars'=>['flags' = ENT_QUOTES|ENT_SUBSTITUTE]]。
   *  例2 ['htmlspecialchars'=>[ENT_QUOTES|ENT_SUBSTITUTE]]。
   *  例3 ['htmlspecialchars','strip_tags'=>null]。
   * @var array{string:array} 全局过滤方法
   */
  protected array $filter = [
    'htmlspecialchars' => null
  ];
  /**
   * @var UriInterface Uri实例
   */
  protected UriInterface $uri;
  /**
   * @var StreamInterface body流
   */
  protected StreamInterface $stream;

  protected function __construct(protected swooleRequest $swooleRequest)
  {
  }

  /**
   * 该方法用于容器中获取当前请求对象
   *
   * @return static
   */
  public static function __make(): static
  {
    return Context::get('request', Coroutine::getTopId());
  }

  /**
   * 获取get参数
   * @param string|null $key 要获取的参数
   * @param mixed|null $default 默认值
   * @return mixed
   */
  public function get(?string $key = null, mixed $default = null): mixed
  {
    if (!empty($key)) {
      return $this->swooleRequest->get[$key] ?? $default;
    } else {
      return $this->swooleRequest->get ?? $default;
    }
  }

  /**
   * 当前是否JSON请求
   * @access public
   * @return bool
   */
  public function isJson(): bool
  {
    $accept = $this->getHeaderLine('accept');
    $types = explode(',', self::ACCEPT_TYPE['json']);
    foreach ($types as $type) {
      if (stristr($accept, $type)) return true;
    }
    return false;
  }

  /**
   * 检索单个标头的值的逗号分隔字符串。
   *
   * @param string $name 不区分大小写的标头字段名称。
   * @return string 作为给定标头的字符串值，使用逗号拼接在一起。如果消息中没有出现标头，则此方法必须返回一个空字符串。
   */
  public function getHeaderLine(string $name): string
  {
    return $this->swooleRequest->header[strtolower($name)] ?? '';
  }

  /**
   * 获取请求标头
   *
   * @param string $name
   * @param string|null $default
   * @return mixed
   */
  public function header(string $name, string $default = null): mixed
  {
    return $this->swooleRequest->header[$name] ?? $default;
  }

  /**
   * 批量获取请求参数
   *
   * @access public
   * @param string|array{string,mixed}|string[]|null $rule 可传key或[key=>default,...]或[key1,key2....]
   * @param bool $isShowNull 是否显示为null的字段
   * @return array
   */
  public function getParams(array|string|null $rule = null, bool $isShowNull = true): array
  {
    $params = [];
    if (empty($rule)) {
      $params = $this->param();
    } elseif (is_string($rule)) {
      $params[$rule] = $this->param($rule);
    } else {
      foreach ($rule as $key => $val) {
        [$paramName, $defaultVal] = is_int($key) ? [$val, null] : [$key, $val];
        $params[$paramName] = $this->param($paramName, $defaultVal);
      }
    }
    return $isShowNull ? $params : array_filter($params, function ($value) {
      return !is_null($value);
    });
  }

  /**
   * 获取请求参数
   * @param string|null $key
   * @param mixed $default
   * @param string|null $filter
   * @return mixed
   */
  public function param(?string $key = null, mixed $default = null, string $filter = null): mixed
  {
    if ($this->getMethod() !== 'GET') {
      $data = $this->post($key, $default);
    } else {
      $data = $this->get($key, $default);
    }
    if (is_string($data)) {
      $data = $this->filter($data, $filter);
    }
    return $data;
  }

  /**
   * 检索请求的 HTTP 方法。
   *
   * @return string 返回请求方法。
   */
  public function getMethod(): string
  {
    return $this->swooleRequest->getMethod() ?? '';
  }

  /**
   * 获取post参数
   * @param string|null $key 要获取的参数
   * @param mixed|null $default 默认值
   * @return mixed
   */
  public function post(?string $key = null, mixed $default = null): mixed
  {
    if (!empty($key)) {
      return $this->swooleRequest->post[$key] ?? $default;
    } else {
      return $this->swooleRequest->post ?? [];
    }
  }

  /**
   * 过滤数据
   *
   * @param string $data
   * @param array|string|null $filter
   * @return string
   */
  public function filter(string $data, array|string|null $filter = null): string
  {
    if (!empty($filter)) {
      if (is_string($filter)) {
        $filters = [$filter => null];
      } else {
        $filters = $filter;
      }
    } else {
      $filters = $this->filter ?? [];
    }
    foreach ($filters as $fun => $arguments) {
      if (is_int($fun)) {
        $fun = $arguments;
        $arguments = [];
      } elseif (empty($arguments)) {
        $arguments = [];
      }
      if (function_exists($fun)) {
        $data = $fun($data, ...$arguments);
      }
    }
    return $data;
  }

  /**
   * 获取服务参数。
   *
   * 该方法返回\Swoole\Http\Request::server属性，相当于 PHP 的 $_SERVER 数组。
   *
   * @return array
   */
  public function getServerParams(): array
  {
    return $this->swooleRequest->server;
  }

  /**
   * 检索 Cookie。
   *
   * 检索客户端发送到服务器的 Cookie。
   *
   * 该方法返回\Swoole\Http\Request::cookie属性，结构为键值对数组。
   *
   * @return array
   */
  public function getCookieParams(): array
  {
    return $this->swooleRequest->cookie ?? [];
  }

  /**
   * 返回具有指定 Cookie 的实例。
   *
   * @param array $cookies 表示 Cookie 的键值对数组。
   * @return static
   */
  public function withCookieParams(array $cookies): RequestInterface
  {
    $newRequest = clone $this;
    $newRequest->swooleRequest->cookie = $cookies;
    return $newRequest;
  }

  public function __clone(): void
  {
    $newSwooleRequest = swooleRequest::create();

    // 复制原始请求的数据到新请求对象
    $newSwooleRequest->fd = $this->swooleRequest->fd;
    $newSwooleRequest->streamId = $this->swooleRequest->streamId;
    $newSwooleRequest->header = $this->swooleRequest->header;
    $newSwooleRequest->server = $this->swooleRequest->server;
    $newSwooleRequest->cookie = $this->swooleRequest->cookie;
    $newSwooleRequest->get = $this->swooleRequest->get;
    $newSwooleRequest->files = $this->swooleRequest->files;
    $newSwooleRequest->post = $this->swooleRequest->post;
    $newSwooleRequest->tmpfiles = $this->swooleRequest->tmpfiles;
    $this->swooleRequest = $newSwooleRequest;
  }

  /**
   * 创建request对象
   *
   * @access public
   * @param swooleRequest|null $request
   * @return Request
   */
  public static function create(?swooleRequest $request = null): static
  {
    $instance = Context::get('request', null, Coroutine::getTopId());
    if (is_null($instance)) {
      if (is_null($request)) $request = swooleRequest::create();
      $contentType = $request->header['content-type'] ?? null;
      if ($contentType === 'application/json') {
        // 获取原始请求内容
        $rawContent = $request->rawContent();
        // 解析JSON数据
        $postData = json_decode($rawContent, true);
        // 将解析后的数据设置到 $request->post
        $request->post = $postData;
      }
      //接管swoole源Request对象
      $requestClass = Request::class;
      if (class_exists('\App\Request')) $requestClass = \App\Request::class;
      $instance = new $requestClass($request);
      Context::set('request', $instance, Coroutine::getTopId());
    }
    return $instance;
  }

  /**
   * 检索查询字符串参数。
   *
   * 检索反序列化的查询字符串参数（如果有的话）。
   *
   * 注意：查询参数可能与 URI 或服务器参数不同步。
   *
   * @return array
   */
  public function getQueryParams(): array
  {
    if (!empty($this->swooleRequest->server['query_string'])) {
      $queryString = $this->swooleRequest->server['query_string'];
      $parameters = [];
      // 解析查询字符串并将其转换为关联数组
      parse_str($queryString, $parameters);
      return $parameters;
    } else {
      return [];
    }
  }

  /**
   * 返回具有指定查询字符串参数的实例。
   *
   * 这些值应该在传入请求的过程中保持不可变。它们可以在实例化期间注入，例如来自 PHP 的 $_GET 超全局变量，或者可以从 URI 等其他值派生而来。在从 URI 解析参数的情况下，数据必须与 PHP 的 parse_str() 返回的数据结构兼容，以便处理重复的查询参数以及嵌套集的处理方式。
   *
   * 设置查询字符串参数不得更改存储在请求中的 URI，也不得更改服务器参数中的值。
   *
   * 此方法必须以保持消息的不可变性的方式实现，并且必须返回具有已更新的查询字符串参数的实例。
   *
   * @param array $query 查询字符串参数的键/值对数组，通常来自于 $_GET。
   * @return static
   */
  public function withQueryParams(array $query): RequestInterface
  {
    $newRequest = clone $this;
    $query = http_build_query($query);
    $newRequest->swooleRequest->server['query_string'] = $query;
    return $newRequest;
  }

  /**
   * 检索标准化的文件上传数据。
   *
   * @return UploadedFileInterface[] 包含 UploadedFileInterface 实例的树形数组；如果没有数据存在，必须返回空数组。
   */
  public function getUploadedFiles(): array
  {
    return $this->swooleRequest->files ?? [];
  }

  /**
   * 创建具有指定上传文件的新实例。
   *
   * 此方法必须以保持消息的不可变性的方式实现，并且必须返回具有已更新的 body 参数的实例。
   *
   * @param array $uploadedFiles 包含 UploadedFileInterface 实例的树形数组。
   * @return static
   * @throws InvalidArgumentException 如果上传文件结构无效
   */
  public function withUploadedFiles(array $uploadedFiles): RequestInterface
  {
    $this->validateUploadedFilesStructure($uploadedFiles);
    $newRequest = clone $this;
    $newRequest->swooleRequest->files = $uploadedFiles;
    return $newRequest;
  }

  /**
   * 验证uploadedFiles结构
   *
   * @param array $uploadedFiles
   * @return void
   * @throws InvalidArgumentException 如果文件结构无效
   */
  protected function validateUploadedFilesStructure(array $uploadedFiles): void
  {
    foreach ($uploadedFiles as $fieldData) {
      if (is_array($fieldData)) {
        $this->validateUploadedFilesStructure($fieldData); // 递归验证多维结构
      } elseif (!$fieldData instanceof UploadedFileInterface) {
        throw new InvalidArgumentException('Invalid uploaded file structure');
      }
    }
  }

  /**
   * 检索请求体中提供的参数。
   *
   * @return null|array 如果有的话，返回已反序列化的 body 参数。这通常是数组或对象。
   */
  public function getParsedBody(): array|null
  {
    return $this->swooleRequest->post;
  }

  /**
   * 返回具有指定 body 参数的实例。
   *
   * @param null|array|object $data 已反序列化的 body 数据。通常是数组或对象。
   * @return static
   * @throws InvalidArgumentException 如果提供了不支持的参数类型。
   */
  public function withParsedBody($data): RequestInterface
  {
    if (!is_null($data) && !is_array($data) && !is_object($data)) {
      throw new InvalidArgumentException(
        'Invalid data type provided; must be null, array, or object'
      );
    }
    $newRequest = clone $this;
    $newRequest->swooleRequest->post = $data;
    return $newRequest;
  }

  /**
   * 检索单个派生的请求属性。
   *
   * @param string $name 属性名称。
   * @param mixed $default 如果属性不存在，则返回的默认值。
   * @return mixed
   * @see getAttributes()
   */
  public function getAttribute(string $name, $default = null): mixed
  {
    return $this->getAttributes()[$name] ?? $default;
  }

  /**
   * 检索从请求派生的属性。
   *
   * @return array{
   *   header:array,
   *   server:array,
   *   cookie:array,
   *   get:array,
   *   files:array,
   *   post:array,
   *   tmpfiles:array,
   * } 从请求派生的属性。
   */
  public function getAttributes(): array
  {
    return [
      'header' => $this->swooleRequest->header,
      'server' => $this->swooleRequest->server,
      'cookie' => $this->swooleRequest->cookie ?? [],
      'get' => $this->swooleRequest->get ?? [],
      'files' => $this->swooleRequest->files ?? [],
      'post' => $this->swooleRequest->post ?? [],
      'tmpfiles' => $this->swooleRequest->tmpfiles ?? []
    ];
  }

  /**
   * 返回具有指定派生请求属性的实例。
   *
   * 此方法允许设置单个派生的请求属性，如在 getAttributes() 中描述。
   *
   * 此方法必须以保持消息的不可变性的方式实现，并且必须返回具有已更新属性的实例。
   *
   * @param string $name 属性名称。
   * @param mixed $value 属性的值。
   * @return static
   * @throws InvalidArgumentException 如果属性未知。
   * @see getAttributes()
   */
  public function withAttribute(string $name, $value): RequestInterface
  {
    if (!array_key_exists($name, $this->getAttributes())) {
      throw new InvalidArgumentException(
        "Unknown attribute $name"
      );
    }
    $newRequest = clone $this;
    $newRequest->swooleRequest->{$name} = $value;
    return $newRequest;
  }

  /**
   * 返回删除指定派生请求属性的实例。
   *
   * 此方法允许删除单个派生的请求属性，如在 getAttributes() 中描述。
   *
   * 此方法必须以保持消息的不可变性的方式实现，并且必须返回已删除属性的实例。
   *
   * @param string $name 属性名称。
   * @return RequestInterface
   * @see getAttributes()
   */
  public function withoutAttribute(string $name): RequestInterface
  {
    $newRequest = clone $this;
    if (isset($newRequest->swooleRequest->{$name})) {
      unset($newRequest->swooleRequest->{$name});
    }
    return $newRequest;
  }

  /**
   * 获取基本身份验证票据
   *
   * @access public
   * @return array|null AssociativeArray(username,password)
   */
  public function getBasicAuthCredentials(): ?array
  {
    $userinfo = $this->getHeader('Authorization');
    if (!empty($userinfo)) {
      foreach ($userinfo as $value) {
        // 获取请求头部中的 "Authorization" 字段的值
        $authorizationHeader = $value;
        // 检查是否包含 "Basic " 前缀
        if (str_starts_with($authorizationHeader, 'Basic ')) {
          // 去除 "Basic " 前缀并解码 Base64 编码的字符串
          $base64Credentials = substr($authorizationHeader, 6);
          $credentials = base64_decode($base64Credentials);
          if ($credentials !== false) {
            // 分离用户名和密码
            return explode(':', $credentials, 2);
          }
        }
      }
    }
    return null;
  }

  /**
   * 通过给定的不区分大小写的名称检索消息头值。
   *
   * 该方法返回给定不区分大小写的标头名称的所有标头值的数组。
   *
   * 如果消息中没有出现标头，则此方法返回一个空数组。
   *
   * @param string $name 不区分大小写的标头字段名称。
   * @return string[] 作为给定标头的字符串值的数组。如果消息中没有出现标头，则此方法必须返回一个空数组。
   */
  public function getHeader(string $name): array
  {
    return Header::getHeader($name, $this->swooleRequest->header);
  }

  /**
   * 获取消息的请求目标。
   *
   * @return string
   */
  public function getRequestTarget(): string
  {
    return ($this->swooleRequest->server['path_info'] ?? $this->swooleRequest->server['request_uri']) ?? '/';
  }

  /**
   * 返回具有指定请求目标的实例。
   *
   * 如果请求需要非 origin-form 请求目标（例如，为了指定 absolute-form、authority-form 或 asterisk-form），则可以使用此方法创建具有指定请求目标的实例。
   *
   * 此方法必须以保持消息的不可变性的方式实现，并且必须返回具有更改的请求目标的实例。
   *
   * @link http://tools.ietf.org/html/rfc7230#section-5.3（用于请求消息中允许的各种请求目标形式）
   * @param string $requestTarget
   * @return static
   */
  public function withRequestTarget(string $requestTarget): RequestInterface
  {
    $newRequest = clone $this;

    // 设置新地请求目标
    $newRequest->swooleRequest->server['path_info'] = $requestTarget;
    $newRequest->swooleRequest->server['request_uri'] = $requestTarget;
    return $newRequest;
  }

  /**
   * 返回具有提供的 HTTP 方法的实例。
   *
   * @param string $method 请求方法。
   * @return static
   * @throws InvalidArgumentException 用于无效的 HTTP 方法。
   */
  public function withMethod(string $method): RequestInterface
  {
    $newRequest = clone $this;
    $newRequest->swooleRequest->server['request_method'] = strtoupper($method);
    return $newRequest;
  }

  /**
   * 检索 URI 实例。
   *
   * @return UriInterface 返回表示请求 URI 的 UriInterface 实例。
   */
  public function getUri(): UriInterface
  {
    if (!isset($this->uri)) {
      $this->uri = Uri::create($this);
    }
    return $this->uri;
  }

  /**
   * 返回具有提供的 URI 的实例。
   *
   * @link http://tools.ietf.org/html/rfc3986#section-4.3
   * @param UriInterface $uri 要使用的新请求 URI。
   * @param bool $preserveHost 保留 Host 标头的原始状态。
   * @return static
   */
  public function withUri(UriInterface $uri, bool $preserveHost = false): RequestInterface
  {
    $newRequest = clone $this;
    $newRequest->uri = $uri;
    if (!$preserveHost) $newRequest->swooleRequest->header['host'] = $uri->getAuthority();
    return $newRequest;
  }

  /**
   * 获取HTTP协议版本。
   *
   * 字符串必须只包含HTTP版本号（例如，"1.1"，"1.0"）。
   *
   * @return string HTTP协议版本。
   */
  public function getProtocolVersion(): string
  {
    $arr = explode('/', $this->swooleRequest->server['server_protocol']);
    return count($arr) === 2 ? $arr[1] : $arr[0];
  }

  /**
   * 返回指定的HTTP协议版本的新实例。
   *
   * 版本字符串必须只包含HTTP版本号（例如，"1.1"，"1.0"）。
   *
   * 此方法必须以保持消息的不可变性的方式实现，并且必须返回具有新协议版本的实例。
   *
   * @param string $version HTTP协议版本
   * @return static
   */
  public function withProtocolVersion(string $version): static
  {
    // 使用Swoole的create方法创建新的请求对象
    $newRequest = clone $this;

    // 设置新地请求目标
    $newRequest->swooleRequest->server['server_protocol'] = $version;

    // 创建自定义请求对象并返回
    return $newRequest;
  }

  /**
   * 通过给定的不区分大小写的名称检查标头是否存在。
   *
   * @param string $name 不区分大小写的标头字段名称。
   * @return bool 如果任何标头名称使用不区分大小写的字符串比较与给定的标头名称匹配，则返回true。如果消息中没有找到匹配的标头名称，则返回false。
   */
  public function hasHeader(string $name): bool
  {
    $lowercaseArray = array_change_key_case($this->swooleRequest->header);
    return array_key_exists(strtolower($name), $lowercaseArray);
  }

  /**
   * 检索所有消息标头的值的逗号分隔字符串。
   *
   * 该方法返回给定不区分大小写的标头名称的所有标头值作为一个字符串，使用逗号拼接在一起。
   *
   * @access public
   * @param string $formatTheHeader lower|upper|title
   * @return array<string,string> 关联数组，键为小写标头值为字符串使用逗号拼接在一起
   */
  public function getHeaderLines(string $formatTheHeader = 'lower'): array
  {
    return Header::getHeaders($this->swooleRequest->header, 'string', $formatTheHeader);
  }

  /**
   * 检索所有消息头的值。
   *
   * 键表示将在传输中发送的标题名称，而每个值是与该标题相关联的字符串数组。
   * @param string $formatTheHeader lower|upper|title
   * @return array{string,string[]} 返回消息头的关联数组。每个键必须是一个标题名称，每个值必须是该标题的字符串数组。
   */
  public function getHeaders(string $formatTheHeader = 'lower'): array
  {
    return Header::getHeaders($this->swooleRequest->header, 'array', $formatTheHeader);
  }

  /**
   * 使用提供的值替换指定标头的实例。
   *
   * @param string $name 不区分大小写的标头字段名称。
   * @param string|string[] $value 标头值。
   * @return static
   * @throws InvalidArgumentException 对于无效的标头名称或值时。
   */
  public function withHeader(string $name, $value): RequestInterface
  {
    Header::validate($name, $value);
    $newRequest = clone $this;
    $newRequest->swooleRequest->header[strtolower($name)] = is_array($value)
      ? implode(',', $value)
      : $value;
    return $newRequest;
  }

  /**
   * 返回附加了给定值的指定标头的实例。
   *
   * @param string $name 不区分大小写的标头字段名称。
   * @param string|string[] $value 标头值。
   * @return static
   * @throws InvalidArgumentException 对于无效的标头名称或值时。
   */
  public function withAddedHeader(string $name, $value): static
  {
    Header::validate($name, $value);
    $name = strtolower($name);
    $newRequest = clone $this;
    // 添加请求标头
    if (empty($newRequest->swooleRequest->header[$name])) {
      $newRequest->swooleRequest->header[$name] = is_array($value)
        ? implode(',', $value)
        : $value;
    } else {
      $newRequest->swooleRequest->header[$name] .= ",$value";
    }
    return $newRequest;
  }

  /**
   * 返回没有指定标头的实例。
   *
   * @param string $name 要删除的不区分大小写的标头字段名称。
   * @return static
   */
  public function withoutHeader(string $name): static
  {
    $newRequest = clone $this;
    if (is_array($newRequest->swooleRequest->header)) {
      $lowercaseKey = strtolower($name);
      // 将数组键统一转换为小写并检查是否存在
      $lowercaseArray = array_change_key_case($newRequest->swooleRequest->header);
      $realKey = array_search($lowercaseKey, array_keys($lowercaseArray));
      if ($realKey !== false) {
        $keys = array_keys($newRequest->swooleRequest->header);
        $key = $keys[$realKey];
        unset($newRequest->swooleRequest->header[$key]);
      }
    }
    return $newRequest;
  }

  /**
   * 获取消息的主体。
   *
   * 注意：获取到的消息主题是请求进入时的传入的主体
   * @return StreamInterface
   */
  public function getBody(): StreamInterface
  {
    if (!isset($this->stream)) {
      $content = $this->swooleRequest->getContent() ?: '';
      $this->stream = FileStream::create('php://memory', 'r+');
      $this->stream->write($content);
    }
    return $this->stream;
  }

  /**
   * 返回具有指定消息主体的实例。
   *
   * 主体必须是一个StreamInterface对象。
   *
   * 此方法必须以保持消息的不可变性的方式实现，并且必须返回具有新主体流的新实例。
   *
   * @param StreamInterface $body 主体。
   * @return static
   * @throws InvalidArgumentException 当主体无效时。
   */
  public function withBody(StreamInterface $body): static
  {
    // 检查主体是否有效
    $newRequest = clone $this;
    $newRequest->stream = $body;
    return $newRequest;
  }

  /**
   * 获取客户端ip
   * @return string
   */
  public function ip(): string
  {
    return $this->swooleRequest->header['x-real-ip'] ?? $this->swooleRequest->server['remote_addr'];
  }

  /**
   * 添加请求参数
   *
   * @access public
   * @param array $params
   * @return void
   */
  public function addParams(array $params): void
  {
    if ($this->swooleRequest->getMethod() === 'GET') {
      $oldParams = $this->swooleRequest->get ?? [];
      $this->swooleRequest->get = array_merge($oldParams, $params);
    } else {
      $oldParams = $this->swooleRequest->post ?? [];
      $this->swooleRequest->post = array_merge($oldParams, $params);
    }
  }

  /**
   * 判断是否https访问
   *
   * @return bool
   */
  public function https(): bool
  {
    return Server::getServer()->ssl;
  }

  /**
   * 获取\Swoole\Http\Request
   *
   * @access public
   * @return swooleRequest
   */
  public function getSwooleRequest(): swooleRequest
  {
    return $this->swooleRequest;
  }

  /**
   * 获取访问资源路径
   *
   * @access public
   * @return string
   */
  public function getPath(): string
  {
    return $this->swooleRequest->server['path_info'];
  }

  /**
   * 当前请求的资源类型
   *
   * @access public
   * @return string 如果返回*则代表任何类型
   */
  public function getAcceptType(): string
  {
    $accept = $this->getHeaderLine('accept');
    if (empty($accept)) return '*';
    foreach (self::ACCEPT_TYPE as $key => $val) {
      $types = explode(',', $val);
      foreach ($types as $type) {
        if (stristr($accept, $type)) return $key;
      }
    }
    return '*';
  }
}
