<?php


namespace App\util;


class CodeResponse
{
    const SUCCESS = [0, '成功'];
    const FAIL = [-1, '失败'];

    const INVALID_PARAM = [4001, '参数不正确'];
    const PARAM_NOT_EMPTY = [4002, '参数不能为空'];
    const NOT_LOGIN = [5001, '未登录'];
    const UPDATED_FAIL = [5005,'更新失败'];
    const SYSTEM_ERROR    = [5002, '系统内部错误'];

    const AUTH_INVALID_ACCOUNT = [7000,'账户不正确'];
    const AUTH_CAPTCHA_UNSUPPORT = [7001,'验证码服务不支持'];
    const AUTH_CAPTCHA_FREQUENCY = [7002,'验证码发送太频繁'];
    const AUTH_CAPTCHA_UNMATCH = [7003,'验证码不正确'];
    const AUTH_NAME_REGISTERED = [7004,'用户名已注册'];
    const AUTH_MOBILE_REGISTERED = [7005,'手机号已注册'];
    const AUTH_MOBILE_UNREGISTERED = [7006,'手机号未注册'];
    const AUTH_INVALID_MOBILE = [7007, '手机号格式不正确'];
    const AUTH_OPENID_UNACCESS = [7008, 'openid无效'];
    const AUTH_OPENID_BINDED = [7009, 'openid已被绑定'];

    const GOODS_UNSHELVE = [710, '商品已经下架!'];
    const GOODS_NO_STOCK = [711, '商品库存不足!'];
    const GOODS_UNKNOWN  = [712, ''];
    const GOODS_INVALID  = [713, ''];

    const ORDER_UNKNOWN       = [720, '订单不存在'];
    const ORDER_INVALID       = [721, ''];
    const ORDER_CHECKOUT_FAIL = [722, ''];
    const ORDER_CANCEL_FAIL   = [723, ''];
    const ORDER_PAY_FAIL      = [724, ''];

    const ORDER_INVALID_OPERATION = [725, ''];
    const ORDER_COMMENTED         = [726, ''];
    const ORDER_COMMENT_EXPIRED   = [727, ''];

    const GROUPON_EXPIRED = [730, '团购已过期!'];
    const GROUPON_OFFLINE = [731, '团购已下线!'];
    const GROUPON_FULL    = [732, '参团人数已满!'];
    const GROUPON_JOIN    = [733, '团购活动已经参加!'];

    const COUPON_EXCEED_LIMIT = [740, '优惠券已领完'];
    const COUPON_RECEIVE_FAIL = [741, ''];
    const COUPON_CODE_INVALID = [742, ''];

    const AFTERSALE_UNALLOWED      = [750, ''];
    const AFTERSALE_INVALID_AMOUNT = [751, ''];
    const AFTERSALE_INVALID_STATUS = [752, ''];
}
