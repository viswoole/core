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

abstract class Status
{
  public const int CONTINUE = 100;

  public const int SWITCHING_PROTOCOLS = 101;

  public const int PROCESSING = 102;

  public const int OK = 200;

  public const int CREATED = 201;

  public const int ACCEPTED = 202;

  public const int NON_AUTHORITATIVE_INFORMATION = 203;

  public const int NO_CONTENT = 204;

  public const int RESET_CONTENT = 205;

  public const int PARTIAL_CONTENT = 206;

  public const int MULTI_STATUS = 207;

  public const int ALREADY_REPORTED = 208;

  public const int IM_USED = 226;

  public const int MULTIPLE_CHOICES = 300;

  public const int MOVED_PERMANENTLY = 301;

  public const int FOUND = 302;

  public const int SEE_OTHER = 303;

  public const int NOT_MODIFIED = 304;

  public const int USE_PROXY = 305;

  public const int SWITCH_PROXY = 306;

  public const int TEMPORARY_REDIRECT = 307;

  public const int PERMANENT_REDIRECT = 308;

  public const int BAD_REQUEST = 400;

  public const int UNAUTHORIZED = 401;

  public const int PAYMENT_REQUIRED = 402;

  public const int FORBIDDEN = 403;

  public const int NOT_FOUND = 404;

  public const int METHOD_NOT_ALLOWED = 405;

  public const int NOT_ACCEPTABLE = 406;

  public const int PROXY_AUTHENTICATION_REQUIRED = 407;

  public const int REQUEST_TIME_OUT = 408;

  public const int CONFLICT = 409;

  public const int GONE = 410;

  public const int LENGTH_REQUIRED = 411;

  public const int PRECONDITION_FAILED = 412;

  public const int REQUEST_ENTITY_TOO_LARGE = 413;

  public const int REQUEST_URI_TOO_LARGE = 414;

  public const int UNSUPPORTED_MEDIA_TYPE = 415;

  public const int REQUESTED_RANGE_NOT_SATISFIABLE = 416;

  public const int EXPECTATION_FAILED = 417;

  public const int MISDIRECTED_REQUEST = 421;

  public const int UNPROCESSABLE_ENTITY = 422;

  public const int LOCKED = 423;

  public const int FAILED_DEPENDENCY = 424;

  public const int UNORDERED_COLLECTION = 425;

  public const int UPGRADE_REQUIRED = 426;

  public const int PRECONDITION_REQUIRED = 428;

  public const int TOO_MANY_REQUESTS = 429;

  public const int REQUEST_HEADER_FIELDS_TOO_LARGE = 431;

  public const int UNAVAILABLE_FOR_LEGAL_REASONS = 451;

  public const int INTERNAL_SERVER_ERROR = 500;

  public const int NOT_IMPLEMENTED = 501;

  public const int BAD_GATEWAY = 502;

  public const int SERVICE_UNAVAILABLE = 503;

  public const int GATEWAY_TIME_OUT = 504;

  public const int HTTP_VERSION_NOT_SUPPORTED = 505;

  public const int VARIANT_ALSO_NEGOTIATES = 506;

  public const int INSUFFICIENT_STORAGE = 507;

  public const int LOOP_DETECTED = 508;

  public const int NOT_EXTENDED = 510;

  public const int NETWORK_AUTHENTICATION_REQUIRED = 511;

  protected static array $reasonPhrases = [
    self::CONTINUE => 'Continue',
    self::SWITCHING_PROTOCOLS => 'Switching Protocols',
    self::PROCESSING => 'Processing',
    self::OK => 'OK',
    self::CREATED => 'Created',
    self::ACCEPTED => 'Accepted',
    self::NON_AUTHORITATIVE_INFORMATION => 'Non-Authoritative Information',
    self::NO_CONTENT => 'No Content',
    self::RESET_CONTENT => 'Reset Content',
    self::PARTIAL_CONTENT => 'Partial Content',
    self::MULTI_STATUS => 'Multi-status',
    self::ALREADY_REPORTED => 'Already Reported',
    self::IM_USED => 'IM Used',
    self::MULTIPLE_CHOICES => 'Multiple Choices',
    self::MOVED_PERMANENTLY => 'Moved Permanently',
    self::FOUND => 'Found',
    self::SEE_OTHER => 'See Other',
    self::NOT_MODIFIED => 'Not Modified',
    self::USE_PROXY => 'Use Proxy',
    self::SWITCH_PROXY => 'Switch Proxy',
    self::TEMPORARY_REDIRECT => 'Temporary Redirect',
    self::PERMANENT_REDIRECT => 'Permanent Redirect',
    self::BAD_REQUEST => 'Bad Request',
    self::UNAUTHORIZED => 'Unauthorized',
    self::PAYMENT_REQUIRED => 'Payment Required',
    self::FORBIDDEN => 'Forbidden',
    self::NOT_FOUND => 'Not Found',
    self::METHOD_NOT_ALLOWED => 'Method Not Allowed',
    self::NOT_ACCEPTABLE => 'Not Acceptable',
    self::PROXY_AUTHENTICATION_REQUIRED => 'Proxy Authentication Required',
    self::REQUEST_TIME_OUT => 'Request Time-out',
    self::CONFLICT => 'Conflict',
    self::GONE => 'Gone',
    self::LENGTH_REQUIRED => 'Length Required',
    self::PRECONDITION_FAILED => 'Precondition Failed',
    self::REQUEST_ENTITY_TOO_LARGE => 'Request Entity Too Large',
    self::REQUEST_URI_TOO_LARGE => 'Request-URI Too Large',
    self::UNSUPPORTED_MEDIA_TYPE => 'Unsupported Media Type',
    self::REQUESTED_RANGE_NOT_SATISFIABLE => 'Requested range not satisfiable',
    self::EXPECTATION_FAILED => 'Expectation Failed',
    self::MISDIRECTED_REQUEST => 'Misdirected Request',
    self::UNPROCESSABLE_ENTITY => 'Unprocessable Entity',
    self::LOCKED => 'Locked',
    self::FAILED_DEPENDENCY => 'Failed Dependency',
    self::UNORDERED_COLLECTION => 'Unordered Collection',
    self::UPGRADE_REQUIRED => 'Upgrade Required',
    self::PRECONDITION_REQUIRED => 'Precondition Required',
    self::TOO_MANY_REQUESTS => 'Too Many Requests',
    self::REQUEST_HEADER_FIELDS_TOO_LARGE => 'Request Header Fields Too Large',
    self::UNAVAILABLE_FOR_LEGAL_REASONS => 'Unavailable For Legal Reasons',
    self::INTERNAL_SERVER_ERROR => 'Internal Server Error',
    self::NOT_IMPLEMENTED => 'Not Implemented',
    self::BAD_GATEWAY => 'Bad Gateway',
    self::SERVICE_UNAVAILABLE => 'Service Unavailable',
    self::GATEWAY_TIME_OUT => 'Gateway Time-out',
    self::HTTP_VERSION_NOT_SUPPORTED => 'HTTP Version not supported',
    self::VARIANT_ALSO_NEGOTIATES => 'Variant Also Negotiates',
    self::INSUFFICIENT_STORAGE => 'Insufficient Storage',
    self::LOOP_DETECTED => 'Loop Detected',
    self::NOT_EXTENDED => 'Not Extended',
    self::NETWORK_AUTHENTICATION_REQUIRED => 'Network Authentication Required',
  ];

  /**
   * 获取全部原因短语
   *
   * @access public
   * @return array|string[]
   */
  public static function getReasonPhrases(): array
  {
    return static::$reasonPhrases;
  }

  /**
   * 检索原因短语
   *
   * @access public
   * @param int $code
   * @return string
   */
  public static function getReasonPhrase(int $code): string
  {
    return static::$reasonPhrases[$code] ?? 'Unknown';
  }
}
