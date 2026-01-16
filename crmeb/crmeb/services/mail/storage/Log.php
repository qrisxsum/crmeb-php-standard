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

namespace crmeb\services\mail\storage;

use crmeb\services\mail\BaseMail;
use think\facade\Log as ThinkLog;

/**
 * 占位驱动：仅记录验证码到日志（方便本地调试）
 */
class Log extends BaseMail
{
    protected function initialize(array $config)
    {
        // 占位驱动无需初始化
    }

    public function sendVerifyCode(string $email, string $code, int $expireMinutes, array $context = []): bool
    {
        ThinkLog::info('[mail.log] sendVerifyCode', [
            'email' => $email,
            'code' => $code,
            'expire_minutes' => $expireMinutes,
            'context' => $context,
        ]);
        return true;
    }
}
