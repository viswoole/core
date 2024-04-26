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

use InvalidArgumentException;
use ReflectionClass;
use Swoole\Server as SwooleServer;
use Swoole\Server\Task as SwooleTask;
use Throwable;
use ViSwoole\Core\Exception\TaskException;
use ViSwoole\Core\Facades\Server;
use ViSwoole\Log\Facade\Log;

/**
 * 任务分发
 *
 * 使用该方法来分发任务，服务必须配置task_enable_coroutine或task_object为true,且只能使用Task::push方法来推送任务。
 *
 * 该方法用于配置在服务的onTask事件回调中，当调用Task::push方法时，就会触发该方法。
 *
 * 配置task_ipc_mode参考 https://wiki.swoole.com/zh-cn/#/server/setting?id=task_ipc_mode
 *
 * 如果需要服务启动的时候继续执行队列中任务需配置message_queue_key参考 https://wiki.swoole.com/zh-cn/#/server/setting?id=message_queue_key
 */
class Task
{
  /**
   * @var array<string,array{queue:bool,handle:callable}> 任务主题列表
   */
  protected array $topics = [];

  private function __construct()
  {
    if (is_file(getAppPath() . '/task.php')) {
      require_once getAppPath() . '/task.php';
    }
  }

  /**
   * 分发任务
   *
   * @param SwooleServer $server
   * @param SwooleTask $task
   * @return false|string
   */
  public static function dispatch(SwooleServer $server, SwooleTask $task): mixed
  {
    try {
      $data = $task->data;
      $topic = $data['topic'];
      $task->data = $data['data'];
      $handle = self::factory()->topics[$topic]['handle'];
      return call_user_func_array($handle, [$server, $task]);
    } catch (Throwable $e) {
      Log::task("$topic handle error: {$e->getMessage()}", $e->getTrace());
      return null;
    }
  }

  /**
   * 工厂单例模式
   */
  public static function factory(): static
  {
    static $instance = null;
    if ($instance === null) $instance = new static();
    return $instance;
  }

  /**
   * 容器make实例化
   */
  public static function __make(): static
  {
    return self::factory();
  }

  /**
   * 批量注册任务主题
   *
   * Example:
   * ```
   * // 邮箱任务类
   * class EmailTask {
   *   // 发送登录验证码
   *   public static function login(SwooleServer $server, SwooleTask $task) {
   *     //处理逻辑
   *     return 'success';
   *   }
   *   // 发送注册验证码
   *   public static function register(SwooleServer $server, SwooleTask $task) {
   *     //处理逻辑
   *     return 'success';
   *   }
   * }
   * // 注册一个任务类
   * Task::addTaskClass('email', EmailTask::class);
   * // 触发任务
   * Task::push('email.login', ['email'=>'xxx@qq.com']);
   * ```
   * @access public
   * @param string $prefix 主题前缀可以为空字符串，如果为空则不加前缀
   * @param string $topic_class 任务主题类名
   * @return void
   * @throws TaskException
   */
  public function registers(string $prefix, string $topic_class): void
  {
    if (!class_exists($topic_class)) throw new TaskException("$topic_class not exists");
    $refClass = new ReflectionClass($topic_class);
    // 获取类的方法
    $methods = $refClass->getMethods();
    foreach ($methods as $method) {
      if (!$method->isPublic()) continue;
      if ($method->isStatic()) {
        $handle = $topic_class . '::' . $method->getName();
      } else {
        $handle = [$topic_class, $method->getName()];
      }
      if (!empty(trim($prefix))) {
        $name = $prefix . '.' . $method->getName();
      } else {
        $name = $method->getName();
      }
      $this->register($name, $handle);
    }
  }

  /**
   * 注册任务
   *
   *  Example:
   *  ```
   *  class SmsCodeTask{
   *    public static function send(\Swoole\Server $server,\Swoole\Server\Task $task){
   *     // $task->data 可以获取到任务投递的数据
   *     $data = $task->data;
   *     // 假设投递任务传递的数据是['phone'=>'13800138000']，$phone的值就会是13800138000
   *     $phone = $data['phone'];
   *     // 执行发送短信业务逻辑
   *     // 如果有返回值，需要监听服务事件onFinish，处理回调, 或在push任务的时候传入callback
   *     // 具体参考https://wiki.swoole.com/zh-cn/#/server/events?id=onfinish
   *     return '发送成功';
   *    }
   *  }
   *  ```
   * @param string $topic 任务主题标识
   * @param callable $handle 任务处理者
   * @return void
   */
  public function register(string $topic, callable $handle): void
  {
    $this->topics[$topic] = $handle;
  }

  /**
   * 推送一个任务
   *
   * @param string $topic 任务主题
   * @param mixed $data 要传递给任务处理者的数据
   * @return void 任务投递成功会返回任务队列id
   * @throws TaskException
   */
  public function push(
    string   $topic,
    mixed    $data,
    int      $workerIndex = -1,
    callable $callback = null
  ): void
  {
    if (!$this->has($topic)) {
      throw new InvalidArgumentException('不存在任务主题: ' . $topic, 404);
    }
    $data = [
      'topic' => $topic,
      'data' => $data
    ];
    // 投递任务
    $result = Server::task(
      $data,
      $workerIndex,
      $callback
    );
    // 投递失败抛出异常
    if ($result === false) {
      throw new TaskException("{$topic}任务投递失败", 500);
    }
  }

  /**
   * 判断任务主题是否存在
   *
   * @param string $topic
   * @return bool
   */
  public function has(string $topic): bool
  {
    return isset($this->topics[$topic]);
  }
}
