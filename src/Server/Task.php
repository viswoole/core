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

namespace ViSwoole\Core\Server;

use Swoole\Server\Task as SwooleTask;

readonly class Task
{
  /**
   * @var mixed 任务接收到数据
   */
  public mixed $data;
  /**
   * @var float|int 任务投递时间
   */
  public float|int $dispatch_time;
  /**
   * @var int swoole任务id
   */
  public int $id;
  /**
   * @var int 任务处理进程id
   */
  public int $worker_id;
  /**
   * @var string|mixed 缓存队列id，在任务执行完成时会自动删除。主要用途是服务重启时自动恢复未执行完的任务
   */
  public string $queue_id;
  /**
   * @var int 该异步任务的一些标志位信息
   */
  public int $flags;
  /**
   * @var string 主题
   */
  public string $topic;

  public function __construct(private SwooleTask $SwooleTask)
  {
    $this->data = $this->SwooleTask->data['data'];
    $this->flags = $this->SwooleTask->flags;
    $this->worker_id = $this->SwooleTask->worker_id;
    $this->dispatch_time = $this->SwooleTask->dispatch_time;
    $this->id = $this->SwooleTask->id;
    $this->queue_id = $this->SwooleTask->data['queueId'];
    $this->topic = $this->SwooleTask->data['topic'];
  }

  /**
   * 序列化任务数据
   *
   * @access public
   * @param mixed $data Task data to be packed.
   * @return string|false The packed task data. Returns false if failed.
   */
  public static function pack(mixed $data): string|false
  {
    return SwooleTask::pack($data);
  }

  /**
   * 反序列化任务数据
   *
   * @param string $data The packed task data.
   * @return mixed The unpacked data. Returns false if failed.
   * @since 5.0.1
   */
  public static function unpack(string $data): mixed
  {
    return SwooleTask::unpack($data);
  }

  /**
   * 完成任务
   *
   * @param mixed $data
   * @return bool
   */
  public function finish(mixed $data = null): bool
  {
//    TaskManager::removeTheQueue($this->queue_id);
    return $this->SwooleTask->finish($data);
  }
}
