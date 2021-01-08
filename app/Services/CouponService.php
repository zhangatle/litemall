<?php


namespace App\Services;


use App\Models\Coupon;
use App\Models\CouponUser;
use App\util\Constant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class CouponService extends BaseService
{
    /**
     * @param int $offset
     * @param int $limit
     * @param string $order
     * @param string $sort
     * @return Coupon[]|Builder[]|Collection
     */
    public function getCouponListByLimit($offset = 0, $limit= 0, $order= "desc", $sort= "created_at") {
        return Coupon::query()->offset($offset)->limit($limit)->orderBy($sort, $order)->get();
    }

    /**
     * 获取用户可用的优惠券
     * @param $userId
     * @return CouponUser[]|Builder[]|Collection
     */
    public function getUsableCoupons($userId)
    {
        return CouponUser::query()->where('user_id', $userId)->where('status', Constant::COUPON_USER_STATUS_USABLE)->get();
    }

    /**
     * 获取优惠券
     * @param $ids
     * @return Coupon[]|Builder[]|Collection
     */
    public function getCouponsByIds($ids)
    {
        return Coupon::query()->whereIn('id', $ids)->get();
    }

    /**
     * 检查优惠券的有效性
     * @param Coupon $coupon
     * @param CouponUser $couponUser
     * @param $price
     * @return bool
     */
    public function checkCouponAndPrice(Coupon $coupon, CouponUser $couponUser, $price): bool
    {
        if (empty($couponUser) || empty($coupon)) {
            return false;
        }
        if ($coupon->id != $couponUser->coupon_id) {
            return false;
        }
        if ($coupon->status != Constant::COUPON_STATUS_NORMAL) {
            return false;
        }
        if (bccomp($coupon->min, $price) == 1) {
            return false;
        }
        $now = now();
        switch ($coupon->time_type) {
            case Constant::COUPON_TIME_TYPE_TIME:
                $start_time = strtotime($coupon->start_time);
                $end_time   = strtotime($coupon->end_time);
                if (!($start_time < time() && $end_time > time())) {
                    return false;
                }
                break;
            case Constant::COUPON_TIME_TYPE_DAYS:
                $expired = Carbon::parse($couponUser->add_time)->addDays($coupon->days);
                if ($now->isAfter($expired)) {
                    return false;
                }
                break;
            default:
                return false;
        }
        return true;
    }

    /**
     * 根据优惠券Id获取优惠券
     * @param $id
     * @return Coupon|Coupon[]|Builder|Builder[]|Collection|Model|null
     */
    public function getCoupon($id)
    {
        return Coupon::query()->find($id);
    }

    /**
     * 获取用户优惠券
     * @param $id
     * @return CouponUser|CouponUser[]|Builder|Builder[]|Collection|Model|null
     */
    public function getCouponUser($id)
    {
        return CouponUser::query()->find($id);
    }

    /**
     * 获取合适的优惠券
     * @param $userId
     * @param $checkedGoodsPrice
     * @param $couponId
     * @param $userCouponId
     * @return array
     */
    public function getUserMeetCoupons($userId, $checkedGoodsPrice, $couponId, $userCouponId): array
    {
        $couponsUsers = CouponService::getInstance()->getUsableCoupons($userId);
        $couponIds    = $couponsUsers->pluck('coupon_id')->toArray();
        $coupons      = CouponService::getInstance()->getCouponsByIds($couponIds)->keyBy('id');
        $couponsUsers = $couponsUsers->filter(function (CouponUser $couponUser) use ($coupons, $checkedGoodsPrice) {
            $coupon = $coupons->get($couponUser->coupon_id);
            return CouponService::getInstance()->checkCouponAndPrice($coupon, $couponUser, $checkedGoodsPrice);
        })->sortByDesc(function (CouponUser $couponUser) use ($coupons) {
            /** @var Coupon $coupon */
            $coupon = $coupons->get($couponUser->coupon_id);
            return $coupon->discount;
        });
        // 这里存在三种情况
        // 1. 用户不想使用优惠券，则不处理
        // 2. 用户想自动使用优惠券，则选择合适优惠券
        // 3. 用户已选择优惠券，则测试优惠券是否合适
        $couponPrice = 0;
        if (is_null($couponId) || $couponId == -1) {
            $userCouponId = -1;
            $couponId     = -1;
        } elseif ($couponId == 0) {
            /** @var CouponUser $couponUser */
            $couponUser   = $couponsUsers->first();
            $couponId     = $couponUser->coupon_id ?? 0;
            $userCouponId = $couponUser->id ?? 0;
            $couponPrice  = CouponService::getInstance()->getCoupon($couponId)->discount ?? 0;
        } else {
            $coupon     = CouponService::getInstance()->getCoupon($couponId);
            $couponUser = CouponService::getInstance()->getCouponUser($userCouponId);
            $isValid    = CouponService::getInstance()->checkCouponAndPrice($coupon, $couponUser, $checkedGoodsPrice);
            if ($isValid) {
                $couponPrice = $coupon->discount ?? 0;
            }
        }
        return [$couponId, $userCouponId, $couponPrice, $couponsUsers->count() ?? 0];
    }
}
