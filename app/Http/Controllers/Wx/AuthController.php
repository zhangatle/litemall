<?php


namespace App\Http\Controllers\Wx;


use App\Models\User;
use App\Services\UserService;
use App\util\CodeResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use App\Exceptions\BusinessException;

class AuthController extends WxController
{
    /**
     * 用户注册
     * @param Request $request
     * @return JsonResponse
     * @throws BusinessException
     */
    public function register(Request $request): JsonResponse
    {
        $username = $request->input("username");
        $password = $request->input("password");
        $mobile = $request->input("mobile");
        $code = $request->input("code");
        if(empty($username) || empty($password) || empty($mobile) || empty($code)) {
            return $this->fail(CodeResponse::INVALID_PARAM);
        }
        $user = UserService::getInstance()->getByUsername($username);
        if(!is_null($user)) {
            return $this->fail(CodeResponse::AUTH_NAME_REGISTERED);
        }
        $validator = Validator::make(['mobile'=>$mobile],['mobile'=>'regex:/^1[0-9]{10}$/']);
        if($validator->fails()) {
            return $this->fail(CodeResponse::AUTH_INVALID_MOBILE);
        }
        $user = UserService::getInstance()->getByMobile($mobile);
        if(!is_null($user)) {
            return $this->fail(CodeResponse::AUTH_MOBILE_REGISTERED);
        }
        UserService::getInstance()->checkCaptcha($mobile, $code);
        $user = new User();
        $user->username = $username;
        $user->password = Hash::make($password);
        $user->mobile = $mobile;
        $user->avatar = "https://yanxuan.nosdn.127.net/80841d741d7fa3073e0ae27bf487339f.jpg?imageView&quality=90&thumbnail=64x64";
        $user->nickname = $username;
        $user->last_login_time = Carbon::now()->toDateTimeString();
        $user->last_login_ip = $request->getClientIp();
        $user->save();
        // todo 新用户发券
        $token = Auth::guard('wx')->login($user);
        return $this->success(['token'=>$token, "userInfo"=>["nickName"=>$username, "avatarUrl"=>$user->avatar]]);
    }

    /**
     * 发送短信验证码
     * @param Request $request
     * @return JsonResponse
     */
    public function regCaptcha(Request $request): JsonResponse
    {
        $mobile = $request->input("mobile");
        if(empty($mobile)) {
            return $this->fail(CodeResponse::INVALID_PARAM);
        }
        $validator = Validator::make(['mobile'=>$mobile],['mobile'=>'regex:/^1[0-9]{10}$/']);
        if($validator->fails()) {
            return $this->fail(CodeResponse::AUTH_INVALID_MOBILE);
        }
        $user = UserService::getInstance()->getByMobile($mobile);
        if(!is_null($user)) {
            return $this->fail(CodeResponse::AUTH_MOBILE_REGISTERED);
        }
        $lock = Cache::add("register_captcha_lock_".$mobile, 1, 180);
        if(!$lock) {
            return $this->fail(CodeResponse::AUTH_CAPTCHA_FREQUENCY);
        }
        $isPass = UserService::getInstance()->checkMobileSendCaptchaCount($mobile);
        if(!$isPass) {
            return $this->fail(CodeResponse::AUTH_CAPTCHA_FREQUENCY);
        }
        $code = UserService::getInstance()->setCaptcha($mobile);
        UserService::getInstance()->sendMsg($mobile,$code);
        return $this->success();
    }

    /**
     * 登录接口
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $username = $request->input("username");
        $password = $request->input("password");
        if(empty($username) || empty($password)) {
            return $this->fail(CodeResponse::INVALID_PARAM);
        }
        $user = UserService::getInstance()->getByUsername($username);
        if(is_null($user)) {
            return $this->fail(CodeResponse::AUTH_INVALID_ACCOUNT);
        }
        $isPass = Hash::check($password, $user->getAuthPassword());
        if(!$isPass) {
            return $this->fail(CodeResponse::AUTH_INVALID_ACCOUNT, '账户密码不正确');
        }
        $user->last_login_time = now()->toDateTimeString();
        $user->last_login_ip = $request->getClientIp();
        if(!$user->save()) {
            return $this->fail(CodeResponse::UPDATED_FAIL);
        }
        $token = Auth::guard('wx')->login($user);
        return $this->success([
            'token'=>$token,
            'userInfo' => [
                'nickName'=>$username,
                'avatarUrl' => $user->avatar,
            ]
        ]);
    }

    /**
     * 退出登录
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        Auth::guard('wx')->logout();
        return $this->success();
    }

    /**
     * 获取用户信息
     * @param Request $request
     * @return JsonResponse
     */
    public function info(Request $request): JsonResponse
    {
        /** @var User  $user */
        $user = $this->user();
        return $this->success([
            'nickName'=>$user->nickname,
            'avatar'=>$user->avatar,
            'gender'=>$user->gender,
            'mobile'=>$user->mobile
        ]);
    }

    /**
     * 重置密码
     * @param Request $request
     * @return JsonResponse
     * @throws BusinessException
     */
    public function reset(Request $request): JsonResponse
    {
        $password = $request->input("password");
        $mobile = $request->input("mobile");
        $code = $request->input("code");
        if(empty($password) || empty($mobile) || empty($code)) {
            return $this->fail(CodeResponse::INVALID_PARAM);
        }
        UserService::getInstance()->checkCaptcha($mobile, $code);
        $user = UserService::getInstance()->getByMobile($mobile);
        if(is_null($user)) {
            return $this->fail(CodeResponse::AUTH_MOBILE_UNREGISTERED);
        }
        $user->password = Hash::make($password);
        return $this->failOrSuccess($user->save(), CodeResponse::UPDATED_FAIL);
    }

    /**
     * 修改个人信息
     * @param Request $request
     * @return JsonResponse
     */
    public function profile(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->user();
        $avatar = $request->input('avatar');
        $gender = $request->input('gender');
        $nickname = $request->input('nickname');
        if(!empty($avatar)) {
            $user->avatar = $avatar;
        }
        if(!empty($gender)) {
            $user->gender = $gender;
        }
        if(!empty($nickname)) {
            $user->nickname = $nickname;
        }
        return $this->failOrSuccess($user->save(), CodeResponse::UPDATED_FAIL);
    }
}
