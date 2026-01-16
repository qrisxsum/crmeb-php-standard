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
namespace app\api\validate\user;

use think\Validate;

/**
 * 邮箱验证码登录相关验证
 */
class EmailLoginValidates extends Validate
{
    protected $rule = [
        'email' => 'require|email',
        'captcha' => 'require|length:6',
    ];

    protected $message = [
        'email.require' => '411600',
        'email.email' => '411601',
        'captcha.require' => '410004',
        'captcha.length' => '410010',
    ];

    public function sceneCode()
    {
        return $this->only(['email']);
    }

    public function sceneLogin()
    {
        return $this->only(['email', 'captcha']);
    }
}
