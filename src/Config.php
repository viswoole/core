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

namespace ViSwoole\Core;

/**
 * 配置文件管理类
 */
class Config
{
  /**
   * 配置参数
   * @var array
   */
  protected array $config = [];
  /**
   * 配置文件目录
   * @var string
   */
  protected string $path;
  /**
   * 配置文件扩展名
   * @var string
   */
  protected string $ext;
  /**
   * @var bool 是否区分大小写
   */
  protected bool $matchCase = true;

  /**
   * @param string|null $path 配置文件所在目录
   * @param string $ext 需加载的配置文件扩展名支持json和php,默认为*全部加载
   * @param bool $matchCase 是否区分大小写
   */
  public function __construct(string $path = null, string $ext = '*', bool $matchCase = true)
  {
    $this->path = $path ?: getRootPath() . '/config/autoload/';
    $this->ext = $ext;
    $this->matchCase = $matchCase;
    $this->loadConfig();
  }

  /**
   * 加载配置文件
   * @return void
   */
  protected function loadConfig(): void
  {
    /**配置文件*/
    $defaultConfigFiles = glob($this->path . '*.' . $this->ext);
    //如果出错了 则赋值为空数组
    if ($defaultConfigFiles === false) $defaultConfigFiles = [];
    $this->config = $this->parse($defaultConfigFiles);
  }

  /**
   * 解析配置文件
   *
   * @access public
   * @param array $files
   * @return array
   */
  protected function parse(array $files): array
  {
    $configs = [];
    foreach ($files as $file) {
      $type = pathinfo($file, PATHINFO_EXTENSION);//文件类型
      $key = pathinfo($file, PATHINFO_FILENAME);//文件名
      $config = match ($type) {
        'php' => include $file,
        'yml', 'yaml' => function_exists('yaml_parse_file') ? yaml_parse_file($file) : [],
        'ini' => parse_ini_file($file, true, INI_SCANNER_TYPED) ?: [],
        'json' => json_decode(file_get_contents($file), true),
        default => [],
      };
      $configs[$key] = isset($configs[$key]) ? array_merge($configs[$key], $config) : $config;
    }

    if (!$this->matchCase) $configs = $this->recursiveArrayKeyToLower($configs);
    $this->config = $configs;
    return $configs;
  }

  /**
   * 递归转换键为小写
   *
   * @param array $array
   * @return array
   */
  protected function recursiveArrayKeyToLower(array $array): array
  {
    $result = [];
    foreach ($array as $key => $value) {
      // 如果值是数组，递归调用函数
      if (is_array($value)) $value = $this->recursiveArrayKeyToLower($value);
      // 将键转换为蛇形
      $newKey = strtolower($key);
      // 将新的键值对添加到结果数组中
      $result[$newKey] = $value;
    }
    return $result;
  }

  /**
   * 检测配置是否存在
   *
   * @access public
   * @param string $name 配置参数名（支持多级配置 .号分割）
   * @return bool 注意：如果检测配置值为null时也会返回false
   */
  public function has(string $name): bool
  {
    if (!$this->matchCase) $name = strtolower($name);
    if (!str_contains($name, '.') && !array_key_exists($name, $this->config)) {
      return false;
    }
    return !is_null($this->get($name));
  }

  /**
   * 获取配置参数 name为null则获取所有配置
   * @access public
   * @param string|null $name 配置名称（支持多级配置 .号分割）
   * @param mixed $default 默认值(null)
   * @return mixed
   */
  public function get(string $name = null, mixed $default = null): mixed
  {
    if (empty($name)) return $this->config;
    // 不区分大小写处理
    $nameParts = (!$this->matchCase)
      ? explode('.', strtolower($name))
      : explode('.', $name);
    $config = $this->config;

    foreach ($nameParts as $part) {
      if (!is_array($config) || !array_key_exists($part, $config)) return $default;
      $config = $config[$part] ?? $default;
      if ($config === $default) {
        break; // 当前层级已找不到有效配置且已返回默认值，无需继续遍历
      }
    }
    return $config;
  }

  /**
   * 设置或更新配置，仅在当前进程中下有效，重启进程则会丢失。
   *
   * @param string|array $key 键
   * @param mixed|null $value 值
   * @return void
   */
  public function set(string|array $key, mixed $value = null): void
  {
    if (is_array($key)) {
      foreach ($key as $k => $v) {
        $this->set($k, $v);
      }
    } else {
      if (!$this->matchCase) $key = strtolower($key);
      $keys = explode('.', $key);
      $refArray = &$this->config;
      foreach ($keys as $k) {
        if (!isset($refArray[$k])) {
          // 如果键不存在，则创建它并将其设置为一个空数组
          $refArray[$k] = [];
        }
        $refArray = &$refArray[$k];
      }
      // 在最后一个子数组中设置新值
      $refArray = $value;
    }
  }
}
