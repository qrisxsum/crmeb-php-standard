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

namespace app\api\controller\v1;

use app\Request;
use app\services\message\notice\SmsService;
use app\services\wechat\WechatServices;
use think\facade\Config;
use think\facade\Log as ThinkLog;
use crmeb\services\CacheService;
use app\services\user\LoginServices;
use think\exception\ValidateException;
use app\api\validate\user\RegisterValidates;
use app\api\validate\user\EmailLoginValidates;
use crmeb\services\mail\Mail as MailService;
use think\facade\Env;

/**
 * 微信小程序授权类
 * Class AuthController
 * @package app\api\controller
 */
class LoginController
{
    protected $services;

    /**
     * LoginController constructor.
     * @param LoginServices $services
     */
    public function __construct(LoginServices $services)
    {
        $this->services = $services;
    }

    /**
     * H5账号登陆
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function login(Request $request)
    {
        [$account, $password, $spread, $agent_id] = $request->postMore([
            'account', 'password', 'spread', ['agent_id', 0]
        ], true);
        if (!$account || !$password) {
            return app('json')->fail(410000);
        }
        if (strlen(trim($password)) < 6 || strlen(trim($password)) > 32) {
            return app('json')->fail(400762);
        }
        return app('json')->success(410001, $this->services->login($account, $password, $spread, $agent_id));
    }

    /**
     * 退出登录
     * @param Request $request
     * @return mixed
     */
    public function logout(Request $request)
    {
        $key = trim(ltrim($request->header(Config::get('cookie.token_name')), 'Bearer'));
        CacheService::delete(md5($key));
        return app('json')->success(410002);
    }

    /**
     * 获取发送验证码key
     * @return mixed
     */
    public function verifyCode()
    {
        $unique = password_hash(uniqid(true), PASSWORD_BCRYPT);
        CacheService::set('sms.key.' . $unique, 0, 300);
        $time = sys_config('verify_expire_time', 1);
        return app('json')->success(['key' => $unique, 'expire_time' => $time]);
    }

    /**
     * 获取图片验证码
     * @param Request $request
     * @return \think\Response
     */
    public function captcha(Request $request)
    {
        ob_clean();
        $rep = captcha();
        $key = app('session')->get('captcha.key');
        $uni = $request->get('key');
        if ($uni) {
            CacheService::set('sms.key.cap.' . $uni, $key, 300);
        }
        return $rep;
    }

    /**
     * 验证验证码是否正确
     * @param $uni
     * @param string $code
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    protected function checkCaptcha($uni, string $code): bool
    {
        $cacheName = 'sms.key.cap.' . $uni;
        if (!CacheService::has($cacheName)) {
            return false;
        }
        $key = CacheService::get($cacheName);
        $code = mb_strtolower($code, 'UTF-8');
        $res = password_verify($code, $key);
        if ($res) {
            CacheService::delete($cacheName);
        }
        return $res;
    }

    /**
     * 验证码发送
     * @param Request $request
     * @param SmsService $services
     * @return mixed
     */
    public function verify(Request $request, SmsService $services)
    {
        [$phone, $type, $key, $captchaType, $captchaVerification] = $request->postMore([
            ['phone', 0],
            ['type', ''],
            ['key', ''],
            ['captchaType', ''],
            ['captchaVerification', ''],
        ], true);

        $keyName = 'sms.key.' . $key;
        if (!CacheService::has($keyName)) return app('json')->fail(410003);

        // 验证限制
        // 验证码每分钟发送上限
        $maxMinuteCountKey = 'sms.minute.' . $phone . date('YmdHi');
        $minuteCount = 0;
        if (CacheService::has($maxMinuteCountKey)) {
            $minuteCount = CacheService::get($maxMinuteCountKey) ?? 0;
            $maxMinuteCount = Config::get('sms.maxMinuteCount', 5);
            if ($minuteCount > $maxMinuteCount) return app('json')->fail('同一手机号每分钟最多发送' . $maxMinuteCount . '条');

        }

        // 验证码单个手机每日发送上限
        $maxPhoneCountKey = 'sms.phone.' . $phone . '.' . date('Ymd');
        $phoneCount = 0;
        if (CacheService::has($maxPhoneCountKey)) {
            $phoneCount = CacheService::get($maxPhoneCountKey) ?? 0;
            $maxPhoneCount = Config::get('sms.maxPhoneCount', 20);
            if ($phoneCount > $maxPhoneCount) return app('json')->fail('同一手机号每天最多发送' . $maxPhoneCount . '条');

        }

        // 验证码单个手机每日发送上限
        $maxIpCountKey = 'sms.ip.' . app()->request->ip() . '.' . date('Ymd');
        $ipCount = 0;
        if (CacheService::has($maxIpCountKey)) {
            $ipCount = CacheService::get($maxIpCountKey) ?? 0;
            $maxIpCount = Config::get('sms.maxIpCount', 50);
            if ($ipCount > $maxIpCount) return app('json')->fail('同一IP每天最多发送' . $maxIpCount . '条');

        }

        //二次验证
        try {
            aj_captcha_check_two($captchaType, $captchaVerification);
        } catch (\Throwable $e) {
            return app('json')->fail($e->getError());
        }

        try {
            validate(RegisterValidates::class)->scene('code')->check(['phone' => $phone]);
        } catch (ValidateException $e) {
            return app('json')->fail($e->getError());
        }
        $time = sys_config('verify_expire_time', 1);
        $smsCode = $this->services->verify($services, $phone, $type, $time);
        if ($smsCode) {
            CacheService::set('code_' . $phone, $smsCode, $time * 60);
            CacheService::set($maxMinuteCountKey, (int)$minuteCount + 1, 61);
            CacheService::set($maxPhoneCountKey, (int)$phoneCount + 1, 86401);
            CacheService::set($maxIpCountKey, (int)$ipCount + 1, 86401);
            return app('json')->success(410007);
        } else {
            return app('json')->fail(410008);
        }

    }

    /**
     * 邮箱验证码发送
     * @param Request $request
     * @return mixed
     */
    public function emailVerify(Request $request)
    {
        [$email, $type, $key, $captchaType, $captchaVerification] = $request->postMore([
            ['email', ''],
            ['type', 'login'],
            ['key', ''],
            ['captchaType', ''],
            ['captchaVerification', ''],
        ], true);

        $keyName = 'sms.key.' . $key;
        if (!CacheService::has($keyName)) return app('json')->fail(410003);

        // 发送限制（占位默认值，可后续调整 config/mail.php）
        $maxMinuteCountKey = 'mail.minute.' . $email . date('YmdHi');
        $minuteCount = CacheService::has($maxMinuteCountKey) ? (CacheService::get($maxMinuteCountKey) ?? 0) : 0;
        $maxMinuteCount = Config::get('mail.maxMinuteCount', 5);
        if ($minuteCount > $maxMinuteCount) return app('json')->fail('同一邮箱每分钟最多发送' . $maxMinuteCount . '条');

        $maxEmailCountKey = 'mail.email.' . $email . '.' . date('Ymd');
        $emailCount = CacheService::has($maxEmailCountKey) ? (CacheService::get($maxEmailCountKey) ?? 0) : 0;
        $maxEmailCount = Config::get('mail.maxEmailCount', 20);
        if ($emailCount > $maxEmailCount) return app('json')->fail('同一邮箱每天最多发送' . $maxEmailCount . '条');

        $maxIpCountKey = 'mail.ip.' . app()->request->ip() . '.' . date('Ymd');
        $ipCount = CacheService::has($maxIpCountKey) ? (CacheService::get($maxIpCountKey) ?? 0) : 0;
        $maxIpCount = Config::get('mail.maxIpCount', 50);
        if ($ipCount > $maxIpCount) return app('json')->fail('同一IP每天最多发送' . $maxIpCount . '条');

        // 二次验证（滑块/人机）
        try {
            aj_captcha_check_two($captchaType, $captchaVerification);
        } catch (\Throwable $e) {
            return app('json')->fail($e->getError());
        }

        try {
            validate(EmailLoginValidates::class)->scene('code')->check(['email' => $email]);
        } catch (ValidateException $e) {
            return app('json')->fail($e->getError());
        }

        // 注册/登录场景校验
        $exists = $this->services->existsEmail($email);
        if ($type === 'register' && $exists) return app('json')->fail(411602);
        if ($type !== 'register' && !$exists) return app('json')->fail(411603);

        $time = (int)sys_config('verify_expire_time', 1);
        $mailCode = (string)rand(100000, 999999);

        // 占位邮件服务：默认写日志，后续可替换为 SMTP 等
        /** @var MailService $mail */
        $mail = app()->make(MailService::class);
        $sent = $mail->sendVerifyCode($email, $mailCode, $time, ['type' => $type]);
        if (!$sent) {
            $error = method_exists($mail, 'getLastError') ? $mail->getLastError() : null;
            if ($error) {
                ThinkLog::error('[mail] emailVerify send failed', [
                    'email' => $email,
                    'type' => $type,
                    'error' => $error,
                ]);
                if (Env::get('app_debug', false)) {
                    return app('json')->fail($error);
                }
            }
            return app('json')->fail(410008);
        }

        CacheService::set('email_code_' . $email, $mailCode, $time * 60);
        CacheService::set($maxMinuteCountKey, (int)$minuteCount + 1, 61);
        CacheService::set($maxEmailCountKey, (int)$emailCount + 1, 86401);
        CacheService::set($maxIpCountKey, (int)$ipCount + 1, 86401);

        // 开发环境便于联调：返回验证码（生产环境不返回）
        if (Env::get('app_debug', false)) {
            return app('json')->success(410007, ['code' => $mailCode]);
        }

        return app('json')->success(410007);
    }

    /**
     * H5注册新用户
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function register(Request $request)
    {
        [$account, $captcha, $password, $spread, $email, $real_name, $nickname, $sex, $verifyType] = $request->postMore([
            ['account', ''],
            ['captcha', ''],
            ['password', ''],
            ['spread', 0],
            ['email', ''],
            ['real_name', ''],
            ['nickname', ''],
            ['sex', 0],
            ['verify_type', 'phone'],
        ], true);
        try {
            validate(RegisterValidates::class)->scene('register')->check([
                'account' => $account,
                'captcha' => $captcha,
                'password' => $password,
                'email' => $email,
                'real_name' => $real_name,
                'nickname' => $nickname,
                'sex' => $sex,
            ]);
        } catch (ValidateException $e) {
            return app('json')->fail($e->getError());
        }
        if (!in_array($verifyType, ['phone', 'email'], true)) {
            return app('json')->fail('verify_type 参数错误');
        }
        if (strlen(trim($password)) < 6 || strlen(trim($password)) > 32) {
            return app('json')->fail(400762);
        }
        $verifyCode = null;
        if ($verifyType === 'email') {
            $verifyCode = CacheService::get('email_code_' . $email);
        } else {
            $verifyCode = CacheService::get('code_' . $account);
        }
        if (!$verifyCode)
            return app('json')->fail(410009);
        $verifyCode = substr($verifyCode, 0, 6);
        if ($verifyCode != $captcha)
            return app('json')->fail(410010);
        if ($verifyType === 'email') {
            CacheService::delete('email_code_' . $email);
        }
        if (md5($password) == md5('123456')) return app('json')->fail(410012);

        $registerStatus = $this->services->register($account, $password, $spread, 'h5', [
            'email' => $email,
            'real_name' => $real_name,
            'nickname' => $nickname,
            'sex' => (int)$sex,
        ]);
        if ($registerStatus) {
            return app('json')->success(410013);
        }
        return app('json')->fail(410014);
    }

    /**
     * 密码修改
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function reset(Request $request)
    {
        [$account, $captcha, $password] = $request->postMore([['account', ''], ['captcha', ''], ['password', '']], true);
        try {
            validate(RegisterValidates::class)->scene('reset')->check(['account' => $account, 'captcha' => $captcha, 'password' => $password]);
        } catch (ValidateException $e) {
            return app('json')->fail($e->getError());
        }
        if (strlen(trim($password)) < 6 || strlen(trim($password)) > 32) {
            return app('json')->fail(400762);
        }
        $verifyCode = CacheService::get('code_' . $account);
        if (!$verifyCode)
            return app('json')->fail(410009);
        $verifyCode = substr($verifyCode, 0, 6);
        if ($verifyCode != $captcha) {
            return app('json')->fail(410010);
        }
        if ($password == '123456') return app('json')->fail(410012);
        $resetStatus = $this->services->reset($account, $password);
        if ($resetStatus) return app('json')->success(100001);
        return app('json')->fail(100007);
    }

    /**
     * 手机号登录
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function mobile(Request $request)
    {
        [$phone, $captcha, $spread, $agent_id] = $request->postMore([['phone', ''], ['captcha', ''], ['spread', 0], ['agent_id', 0]], true);

        //验证手机号
        try {
            validate(RegisterValidates::class)->scene('code')->check(['phone' => $phone]);
        } catch (ValidateException $e) {
            return app('json')->fail($e->getError());
        }

        //验证验证码
        $verifyCode = CacheService::get('code_' . $phone);
        if (!$verifyCode)
            return app('json')->fail(410009);
        $verifyCode = substr($verifyCode, 0, 6);
        if ($verifyCode != $captcha) {
            return app('json')->fail(410010);
        }
        $user_type = $request->getFromType() ? $request->getFromType() : 'h5';
        $token = $this->services->mobile($phone, $spread, $user_type, $agent_id);
        if ($token) {
            CacheService::delete('code_' . $phone);
            return app('json')->success(410001, $token);
        } else {
            return app('json')->fail(410002);
        }
    }

    /**
     * 邮箱验证码登录
     * @param Request $request
     * @return mixed
     */
    public function emailLogin(Request $request)
    {
        [$email, $captcha, $spread, $agent_id] = $request->postMore([
            ['email', ''],
            ['captcha', ''],
            ['spread', 0],
            ['agent_id', 0],
        ], true);

        try {
            validate(EmailLoginValidates::class)->scene('login')->check(['email' => $email, 'captcha' => $captcha]);
        } catch (ValidateException $e) {
            return app('json')->fail($e->getError());
        }

        $verifyCode = CacheService::get('email_code_' . $email);
        if (!$verifyCode) return app('json')->fail(410009);
        $verifyCode = substr((string)$verifyCode, 0, 6);
        if ($verifyCode != $captcha) return app('json')->fail(410010);

        $token = $this->services->emailLogin($email, $spread, $agent_id);
        if ($token) {
            CacheService::delete('email_code_' . $email);
            return app('json')->success(410001, $token);
        }
        return app('json')->fail(410019);
    }

    /**
     * H5切换登陆
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function switch_h5(Request $request)
    {
        $from = $request->post('from', 'wechat');
        $user = $request->user();
        $token = $this->services->switchAccount($user, $from);
        if ($token) {
            $token['userInfo'] = $user;
            return app('json')->success(410001, $token);
        } else
            return app('json')->fail(410002);
    }

    /**
     * 绑定手机号
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function binding_phone(Request $request)
    {
        list($phone, $captcha, $key) = $request->postMore([
            ['phone', ''],
            ['captcha', ''],
            ['key', '']
        ], true);
        //验证手机号
        try {
            validate(RegisterValidates::class)->scene('code')->check(['phone' => $phone]);
        } catch (ValidateException $e) {
            return app('json')->fail($e->getError());
        }
        if (!$key) {
            return app('json')->fail(100100);
        }
        if (!$phone) {
            return app('json')->fail(410015);
        }
        //验证验证码
        $verifyCode = CacheService::get('code_' . $phone);
        if (!$verifyCode)
            return app('json')->fail(410009);
        $verifyCode = substr($verifyCode, 0, 6);
        if ($verifyCode != $captcha) {
            return app('json')->fail(410010);
        }
        $re = $this->services->bindind_phone($phone, $key);
        if ($re) {
            CacheService::delete('code_' . $phone);
            return app('json')->success(410016, $re);
        } else
            return app('json')->fail(410017);
    }

    /**
     * 绑定手机号
     * @param Request $request
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function user_binding_phone(Request $request)
    {
        list($phone, $captcha, $step) = $request->postMore([
            ['phone', ''],
            ['captcha', ''],
            ['step', 0]
        ], true);

        //验证手机号
        try {
            validate(RegisterValidates::class)->scene('code')->check(['phone' => $phone]);
        } catch (ValidateException $e) {
            return app('json')->fail($e->getError());
        }
        if (!$step) {
            //验证验证码
            $verifyCode = CacheService::get('code_' . $phone);
            if (!$verifyCode)
                return app('json')->fail(410009);
            $verifyCode = substr($verifyCode, 0, 6);
            if ($verifyCode != $captcha)
                return app('json')->fail(410010);
        }
        $uid = (int)$request->uid();
        $re = $this->services->userBindindPhone($uid, $phone, $step);
        if ($re) {
            CacheService::delete('code_' . $phone);
            return app('json')->success($re['msg'] ?? 410016, $re['data'] ?? []);
        } else
            return app('json')->fail(410017);
    }

    public function update_binding_phone(Request $request)
    {
        [$phone, $captcha] = $request->postMore([
            ['phone', ''],
            ['captcha', ''],
        ], true);

        //验证手机号
        try {
            validate(RegisterValidates::class)->scene('code')->check(['phone' => $phone]);
        } catch (ValidateException $e) {
            return app('json')->fail($e->getError());
        }
        //验证验证码
        $verifyCode = CacheService::get('code_' . $phone);
        if (!$verifyCode)
            return app('json')->fail(410009);
        $verifyCode = substr($verifyCode, 0, 6);
        if ($verifyCode != $captcha)
            return app('json')->fail(410010);
        $uid = (int)$request->uid();
        $re = $this->services->updateBindindPhone($uid, $phone);
        if ($re) {
            CacheService::delete('code_' . $phone);
            return app('json')->success($re['msg'] ?? 100001, $re['data'] ?? []);
        } else
            return app('json')->fail(100007);
    }

    /**
     * 设置扫描二维码状态
     * @param string $code
     * @return mixed
     */
    public function setLoginKey(string $code)
    {
        if (!$code) {
            return app('json')->fail(410020);
        }
        $cacheCode = CacheService::get($code);
        if ($cacheCode === false || $cacheCode === null) {
            return app('json')->fail(410021);
        }
        CacheService::set($code, '0', 600);
        return app('json')->success();
    }

    /**
     * apple快捷登陆
     * @param Request $request
     * @param WechatServices $services
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function appleLogin(Request $request, WechatServices $services)
    {
        [$openId, $phone, $email, $captcha] = $request->postMore([
            ['openId', ''],
            ['phone', ''],
            ['email', ''],
            ['captcha', '']
        ], true);
        if ($phone) {
            if (!$captcha) {
                return app('json')->fail(410004);
            }
            //验证验证码
            $verifyCode = CacheService::get('code_' . $phone);
            if (!$verifyCode)
                return app('json')->fail(410009);
            $verifyCode = substr($verifyCode, 0, 6);
            if ($verifyCode != $captcha) {
                CacheService::delete('code_' . $phone);
                return app('json')->fail(410010);
            }
        }
        if ($email == '') $email = substr(md5($openId), 0, 12);
        $userInfo = [
            'openId' => $openId,
            'unionid' => '',
            'avatarUrl' => sys_config('h5_avatar'),
            'nickName' => $email,
        ];
        $token = $services->appAuth($userInfo, $phone, 'apple');
        if ($token) {
            return app('json')->success(410001, $token);
        } else if ($token === false) {
            return app('json')->success(410001, ['isbind' => true]);
        } else {
            return app('json')->fail(410019);
        }

    }

    /**
     * 滑块验证
     * @return mixed
     */
    public function ajcaptcha(Request $request)
    {
        $captchaType = $request->get('captchaType');
        return app('json')->success(aj_captcha_create($captchaType));
    }

    /**
     * 一次验证
     * @return mixed
     */
    public function ajcheck(Request $request)
    {
        [$token, $pointJson, $captchaType] = $request->postMore([
            ['token', ''],
            ['pointJson', ''],
            ['captchaType', ''],
        ], true);
        try {
            aj_captcha_check_one($captchaType, $token, $pointJson);
            return app('json')->success();
        } catch (\Throwable $e) {
            return app('json')->fail(400336);
        }
    }

    /**
     * 远程登录接口
     * @param Request $request
     * @return \think\Response
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * @author wuhaotian
     * @email 442384644@qq.com
     * @date 2024/5/21
     */
    public function remoteRegister(Request $request)
    {
        [$remote_token] = $request->getMore([
            ['remote_token', ''],
        ], true);
        if ($remote_token == '') return app('json')->success('登录失败', ['get_remote_login_url' => sys_config('get_remote_login_url')]);
        return app('json')->success('登录成功', $this->services->remoteRegister($remote_token));
    }
}
