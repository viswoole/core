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

namespace ViSwoole\Core\Channel;

use RuntimeException;
use Swoole\Coroutine\Channel;
use ViSwoole\Core\Channel\Contract\ConnectionPoolInterface;
use ViSwoole\Core\Exception\ConnectionPoolException;

/**
 * Abstract ConnectionPool.
 * 连接池抽象类，基于\Swoole\Coroutine\Channel通道实现了连接池的基本功能
 */
abstract class ConnectionPool implements ConnectionPoolInterface
{
  public const int DEFAULT_SIZE = 64;
  public const array ERROR_MESSAGE = [
    SWOOLE_CHANNEL_OK => '正常',
    SWOOLE_CHANNEL_TIMEOUT => '失败：-1 连接超时',
    SWOOLE_CHANNEL_CLOSED => '失败：-2 连接已关闭',
    SWOOLE_CHANNEL_CANCELED => '失败：-3 意外取消'
  ];
  /** @var Channel 当前连接池 */
  protected Channel $pool;

  /**
   * @param int $max_size 连接池长度
   * @param int|null $default_fill 默认填充长度,null为不填充
   */
  public function __construct(
    protected int $max_size = self::DEFAULT_SIZE,
    ?int          $default_fill = null
  )
  {
    $this->pool = new Channel($max_size);
    if ($default_fill) $this->fill($default_fill);
  }

  /**
   * 填充连接
   *
   * @access public
   * @param int|null $size 需要填充的数量(传入的值必须大于当前连接池长度)
   * @return void
   */
  public function fill(int $size = null): void
  {
    $size = $size === null ? $this->max_size : $size;
    while ($size > $this->length()) {
      $this->make();
    }
  }

  /**
   * 获取连接池中当前剩余连接数量
   *
   * @return int
   */
  public function length(): int
  {
    return $this->pool->length();
  }

  /**
   * 往连接池中新增一个连接
   *
   * @return void
   */
  protected function make(): void
  {
    $connection = $this->createConnection();
    $this->put($connection);
  }

  /**
   * 必须实现创建连接方法
   *
   * @return mixed 返回一个可用的连接对象
   */
  abstract protected function createConnection(): mixed;

  /**
   * 归还一个连接到连接池中
   *
   * @param mixed $connection 连接对象（不可用时请归还null）
   * @return void
   */
  public function put(mixed $connection): void
  {
    // 判断返回连接是否为NULL 和 连接是否可用 可用则归还连接
    if ($connection !== null && $this->connectionDetection($connection)) {
      $result = $this->pool->push($connection);
      if ($result === false) throw new ConnectionPoolException(
        self::ERROR_MESSAGE[$this->pool->errCode],
        $this->pool->errCode
      );
    } else {
      // 如果归还的是空连接或不可用则需要重新创建一个新连接填补
      $this->make();
    }
  }

  /**
   * 可实现此方法在获取或归还连接时检测连接是否可用
   *
   * @param mixed $connection
   * @return bool 如果返回true则代表连接可用
   */
  abstract protected function connectionDetection(mixed $connection): bool;

  /**
   * 从连接池中获取一个连接
   *
   * @param float $timeout 超时时间
   * @return mixed
   * @throws RuntimeException 如果获取连接失败则会抛出异常
   */
  public function get(float $timeout = -1): mixed
  {
    if ($this->isEmpty() && $this->length() < $this->max_size) $this->make();
    // 获取连接
    $connection = $this->pool->pop($timeout);
    if ($connection === false) throw new ConnectionPoolException(
      self::ERROR_MESSAGE[$this->pool->errCode],
      $this->pool->errCode
    );
    //判断连接是否可用 如果连接不可用则返回一个新的连接 不可用的连接将会被丢弃
    if (!$this->connectionDetection($connection)) $connection = $this->createConnection();
    return $connection;
  }

  /**
   * 判断连接池中连接是否已经被取完或者为空
   *
   * @return bool
   */
  public function isEmpty(): bool
  {
    return $this->pool->isEmpty();
  }

  /**
   * 判断当前连接池是否已满
   *
   * @access public
   * @return bool
   */
  public function isFull(): bool
  {
    return $this->pool->isFull();
  }

  /**
   * 获取连接池统计信息
   *
   * 返回的数组包含三个字段：
   *   1. consumer_num: 当前static::get()方法正在等待从连接池中获取连接的数量，当连接池已空时会出现。
   *   2. producer_num: 当前static::put()方法正在等待归还到连接池中的数量。当连接池已满时，就会发生这种情况。
   *   3. queue_num: 通道中的元素数。这与语句static::length()的返回值相同。
   *
   *  For example:
   *  [
   *    'consumer_num' => 0, // 目前没有调用get()方法.
   *    'producer_num' => 1, // 连接池已满，并且有一个对put()的方法调用正在等待归还。
   *    'queue_num'    => 2, // 连接池中有两个元素。在这种情况下，连接池的大小也是两个。
   *  ]
   * @return array{
   *   consumer_num: int,
   *   producer_num: int,
   *   queue_num: int,
   * }
   */
  public function stats(): array
  {
    return $this->pool->stats();
  }

  /**
   * 关闭连接池
   *
   * 关闭连接池后的行为：
   *   1. static::get()方法会抛出异常。
   *   2. static::put()方法将会抛出异常。
   *   3. 不能再将连接推入其中，也无法从连接池中弹出连接
   *
   * @return bool
   */
  public function close(): bool
  {
    $result = $this->pool->close();
    if ($result) unset($this->pool);
    return $result;
  }
}
