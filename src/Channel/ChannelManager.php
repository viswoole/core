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

use Override;
use ViSwoole\Core\Contract\ChannelManagerInterface;
use ViSwoole\Core\Contract\ConnectionPoolInterface;
use ViSwoole\Core\Exception\ChannelNotFoundException;
use function Swoole\Coroutine\run;

/**
 * 连接池管理器基类
 */
abstract class ChannelManager implements ChannelManagerInterface
{
  /**
   * @var array{string:ConnectionPoolInterface}
   */
  protected array $channels = [];
  /**
   * @var string 默认连接池
   */
  protected string $defaultChannel;

  /**
   * @param array $channels 通道名称
   * @param string $defaultChannel
   */
  public function __construct(
    array  $channels,
    string $defaultChannel
  )
  {
    $this->defaultChannel = $defaultChannel;
    foreach ($channels as $name => $config) {
      run(function () use ($name, $config) {
        $connect = $this->createPool($config);
        $this->addChannel($name, $connect);
      });
    }
  }

  /**
   * 创建连接池
   *
   * @param mixed $config 配置
   * @return ConnectionPoolInterface
   */
  abstract protected function createPool(mixed $config): ConnectionPoolInterface;

  /**
   * @inheritDoc
   */
  #[Override] public function addChannel(string $name, ConnectionPoolInterface $channel): void
  {
    $this->channels[strtolower($name)] = $channel;
  }

  /**
   * @inheritDoc
   */
  #[Override] public function getChannel(?string $channel_name = null): ConnectionPoolInterface
  {
    if (empty($this->channels)) throw new ChannelNotFoundException('通道列表为空');
    if (empty($channel_alias)) $channel_alias = $this->defaultChannel;
    if ($this->hasChannel($channel_alias)) {
      return $this->channels[strtolower($channel_alias)];
    } else {
      throw new ChannelNotFoundException("通道{$channel_alias}不存在");
    }
  }

  /**
   * @inheritDoc
   */
  #[Override] public function hasChannel(string $channel_name): bool
  {
    return isset($this->channels[strtolower($channel_name)]);
  }

  /**
   * @inheritDoc
   */
  #[Override] public function setDefaultChannel(string $channel_name): void
  {
    if (!$this->hasChannel($channel_name)) throw new ChannelNotFoundException(
      "redis通道{$channel_name}不存在"
    );
    $this->defaultChannel = $channel_name;
  }
}
