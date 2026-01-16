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

use crmeb\basic\BaseStorage;

abstract class BaseMail extends BaseStorage
{
    /**
     * 发送邮箱验证码（用于登录/注册等）
     * @param string $email
     * @param string $code
     * @param int $expireMinutes
     * @param array $context
     * @return bool
     */
    abstract public function sendVerifyCode(string $email, string $code, int $expireMinutes, array $context = []): bool;
}

