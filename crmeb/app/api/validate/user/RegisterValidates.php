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
 * 注册验证
 * Class RegisterValidates
 * @package app\http\validates\user
 */
class RegisterValidates extends Validate
{
    protected $regex = ['phone' => '/^1[3456789]\d{9}$/'];

    protected $rule = [
        'phone' => 'require|regex:phone',
        'account' => 'require|regex:phone',
        'captcha' => 'require|length:6',
        'password' => 'require',
        'email' => 'require|email',
        'real_name' => 'require|max:25',
        'nickname' => 'require|max:60',
        'sex' => 'require|in:0,1,2',
    ];

    protected $message = [
        'phone.require' => '410015',
        'phone.regex' => '410018',
        'account.require' => '410015',
        'account.regex' => '410018',
        'captcha.require' => '410004',
        'captcha.length' => '410010',
        'password.require' => '410011',
        'email.require' => '411600',
        'email.email' => '411601',
        'real_name.require' => '400760',
        'real_name.max' => '411608',
        'nickname.require' => '400001',
        'nickname.max' => '411609',
        'sex.require' => '411607',
        'sex.in' => '411607',
    ];


    public function sceneCode()
    {
        return $this->only(['phone']);
    }


    public function sceneRegister()
    {
        return $this->only(['account', 'captcha', 'password', 'email', 'real_name', 'nickname', 'sex']);
    }

    public function sceneReset()
    {
        return $this->only(['account', 'captcha', 'password']);
    }
}
