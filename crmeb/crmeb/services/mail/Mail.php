<?php
// +----------------------------------------------------------------------
// | CRMEB [ CRMEB赋能开发者，助力企业发展 ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016~2023 https://www.crmeb.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed CRMEB并不是自由软件，未经许可不能去掉CRMEB相关版权
// +----------------------------------------------------------------------
// | Author: CRMEB Team <admin@crmeb.com>
// +----------------------------------------------------------------------

namespace crmeb\services\mail;

use crmeb\basic\BaseManager;
use think\Container;
use think\facade\Config;

/**
 * 邮件发送服务（占位版本：默认 log 驱动）
 */
class Mail extends BaseManager
{
    /**
     * 命名空间
     * @var string
     */
    protected $namespace = '\\crmeb\\services\\mail\\storage\\';

    /**
     * 默认驱动
     * @return mixed
     */
    protected function getDefaultDriver()
    {
        return Config::get('mail.default');
    }

    /**
     * 获取类的实例
     * @param $class
     * @return mixed
     */
    protected function invokeClass($class)
    {
        if (!class_exists($class)) {
            throw new \RuntimeException('class not exists: ' . $class);
        }

        $this->getConfigFile();
        if (!$this->config) {
            $this->config = Config::get($this->configFile . '.stores.' . $this->name, []);
        }

        $handle = Container::getInstance()->invokeClass($class, [$this->name, $this->config, $this->configFile]);
        $this->config = [];
        return $handle;
    }

    /**
     * 获取当前驱动最近一次错误信息（调用后会清空驱动内部 error）
     * @return string|null
     */
    public function getLastError(): ?string
    {
        try {
            $error = $this->driver()->getError();
            return $error !== '' ? $error : null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
