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

use think\facade\Env;

return [
    // 默认驱动（当前为占位实现：记录日志）
    'default' => Env::get('mail.driver', 'log'),

    // 单个邮箱每日发送上限（可后续按需调整）
    'maxEmailCount' => 20,
    // 验证码每分钟发送上限（可后续按需调整）
    'maxMinuteCount' => 5,
    // 单个IP每日发送上限（可后续按需调整）
    'maxIpCount' => 50,

    // 驱动配置
    'stores' => [
        // 占位：仅记录日志（不真正发信）
        'log' => [],
        // 预留：SMTP配置（后续实现）
        'smtp' => [
            'host' => Env::get('mail.host', ''),
            'port' => (int)Env::get('mail.port', 587),
            'username' => Env::get('mail.username', ''),
            'password' => Env::get('mail.password', ''),
            'encryption' => Env::get('mail.encryption', 'tls'),
            'from_email' => Env::get('mail.from_email', ''),
            'from_name' => Env::get('mail.from_name', ''),
            'helo' => Env::get('mail.helo', 'localhost'),
            'timeout' => (int)Env::get('mail.timeout', 10),
        ],
    ],
];
