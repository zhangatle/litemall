<?php


namespace App\Services;


use App\Exceptions\BusinessException;
use App\Models\User;
use App\Notifications\VerificationCode;
use App\util\CodeResponse;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Leonis\Notifications\EasySms\Channels\EasySmsChannel;
use Overtrue\EasySms\PhoneNumber;

class UserService extends BaseService
{
    /**
     * 根据用户名查找用户
     * @param $username
     * @return Builder|Model|object|null
     */
    public function getByUsername($username)
    {
        return User::query()->where("username", $username)->first();
    }

    /**
     * 根据手机号查找用户
     * @param $mobile
     * @return Builder|Model|object|null
     */
    public function getByMobile($mobile)
    {
        return User::query()->where("mobile", $mobile)->first();
    }

    /**
     * 检查验证码发送是否达到上限
     * @param $mobile
     * @return bool
     */
    public function checkMobileSendCaptchaCount($mobile): bool
    {
        $countKey = "register_captcha_count_" . $mobile;
        if (Cache::has($countKey)) {
            $count = Cache::increment($countKey);
            if ($count > 10) {
                return false;
            }
        } else {
            Cache::add($countKey, 1, Carbon::tomorrow()->diffInSeconds(now()));
        }
        return true;
    }

    /**
     * 发送短信验证码
     * @param $mobile
     * @param $code
     */
    public function sendMsg($mobile, $code)
    {
        if (app()->environment('prod')) {
            Cache::put("register_captcha_" . $mobile, $code, 180);
            Notification::route(
                EasySmsChannel::class,
                new PhoneNumber($mobile)
            )->notify(new VerificationCode($code));
        }
    }

    /**
     * 检验验证码
     * @param $mobile
     * @param $code
     * @return bool
     * @throws BusinessException
     */
    public function checkCaptcha($mobile, $code): bool
    {
        $key = 'register_captcha_' . $mobile;
        $isPass = $code === Cache::get($key);
        if ($isPass) {
            return true;
        } else {
            throw new BusinessException(CodeResponse::AUTH_CAPTCHA_UNMATCH);
        }
    }

    /**
     * @param $mobile
     * @return int|string
     */
    public function setCaptcha($mobile)
    {
        if (app()->environment('prod')) {
            $code = strval(mt_rand(100000, 999999));
        } else {
            $code = "123456";
        }
        Cache::put('register_captcha_' . $mobile, $code, 180);
        return $code;
    }

    /**
     * @param $ids
     * @return Builder[]|Collection|\Illuminate\Support\Collection
     */
    public function getUsers($ids)
    {
        if (empty($ids)) {
            return collect([]);
        }
        return User::query()->whereIn('id', $ids)->get();
    }
}
