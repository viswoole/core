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

use ViSwoole\Core\Channel\Contract\ConnectionPoolInterface;
use ViSwoole\Core\Facade;

/**
 * 通道管理门面基类
 *
 * @method static mixed get(float $timeout = -1) 从连接池中获取一个连接
 * @method static void put(mixed $connection) 归还一个连接到连接池中（必须实现）
 * @method static bool isEmpty() 判断连接池中连接是否已经被取完或者为空
 * @method static bool close() 关闭连接池
 * @method static bool isFull() 判断当前连接池是否已满
 * @method static void fill(int $size = null) 填充连接
 * @method static int length() 获取连接池中当前剩余连接数量
 * @method static array stats() 获取连接池统计信息
 * @method static ConnectionPoolInterface getChannel(?string $channel_name = null) 获取通道
 * @method static void setDefaultChannel(string $channel_name) 设置/更改默认通道
 * @method static bool hasChannel(string $channel_name) 判断通道是否存在
 * @method static void addChannel(string $name, ConnectionPoolInterface $channel) 添加通道
 */
abstract class ChannelManagerBaseFacade extends Facade
{

}
