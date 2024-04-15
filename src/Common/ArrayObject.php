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

namespace ViSwoole\Core\Common;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Override;
use Serializable;

class ArrayObject implements ArrayAccess, Countable, IteratorAggregate, Serializable
{

  public function __construct(private array $array = [])
  {
  }

  /**
   * @inheritDoc
   */
  #[Override] public function getIterator(): ArrayIterator
  {
    return new ArrayIterator($this->array);
  }

  /**
   * @inheritDoc
   */
  #[Override] public function offsetSet(mixed $offset, mixed $value): void
  {
    $this->array[$offset] = $value;
  }

  /**
   * @inheritDoc
   */
  #[Override] public function offsetUnset(mixed $offset): void
  {
    unset($this->array[$offset]);
  }

  /**
   * @inheritDoc
   */
  #[Override] public function count(): int
  {
    return count($this->array);
  }

  /**
   * @inheritDoc
   */
  #[Override] public function serialize(): string
  {
    return serialize($this->array);
  }

  /**
   * @inheritDoc
   */
  #[Override] public function unserialize(string $data)
  {
    $this->array = (array)unserialize($data);
    return $this;
  }

  public function __serialize(): array
  {
    return $this->array;
  }

  public function __unserialize(array $data): void
  {
    $this->array = $data;
  }

  /**
   * 交换数组
   *
   * @param array $array
   * @return void
   */
  public function exchangeArray(array $array): void
  {
    $this->array = $array;
  }

  /**
   * 返回数组
   *
   * @access public
   * @return array
   */
  public function toArray(): array
  {
    return $this->array;
  }

  /**
   * @inheritDoc
   */
  #[Override] public function offsetExists(mixed $offset): bool
  {
    return array_key_exists($offset, $this->array);
  }

  public function __get(string $name)
  {
    return $this->offsetGet($name);
  }

  /**
   * @inheritDoc
   */
  #[Override] public function offsetGet(mixed $offset): mixed
  {
    return $this->array[$offset] ?? null;
  }
}
