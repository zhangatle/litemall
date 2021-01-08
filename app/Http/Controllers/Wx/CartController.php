<?php

namespace App\Http\Controllers\Wx;

use App\Exceptions\BusinessException;
use App\Services\AddressService;
use App\Services\CartService;
use App\Services\CouponService;
use App\Services\SystemService;

class CartController extends WxController
{
    /**
     * @throws BusinessException
     */
    public function checkout() {
        $cartId = $this->verifyInteger('cartId');
        $addressId = $this->verifyInteger('addressId');
        $couponId = $this->verifyInteger('couponId');
        $groupRulesId = $this->verifyInteger("grouponRulesId");
        $userCouponId = $this->verifyInteger('userCouponId');
        $userId = $this->userId();
        // 1、收货地址
        $address = AddressService::getInstance()->getAddressOrDefault($userId, $addressId);
        $addressId = $address->id ?? 0;
        // 2、获取商品的列表
        $checkedGoodsList = CartService::getInstance()->getCheckedGoodsList($userId, $addressId);
        // 3、计算团购优惠和商品价格
        $grouponPrice = 0;
        $checkedGoodsPrice = CartService::getInstance()->getCartPriceCutGroupon($checkedGoodsList, $groupRulesId, $groupPrice);
        // 4、获取当前合适的优惠券， 并返回优惠券的折扣和优惠券数量
        list($couponId, $userCouponId, $couponPrice, $countUserCoupon) = CouponService::getInstance()->getUserMeetCoupons($userId, $checkedGoodsPrice, $couponId, $userCouponId);
        // 5、运费
        $freightPrice = SystemService::getInstance()->getFreightPrice($checkedGoodsPrice);
        // 6、计算订单金额
        $orderPrice = bcadd($checkedGoodsPrice, $freightPrice, 1);
        $orderPrice = bcsub($orderPrice, $couponPrice, 1);
        // 7、组装数据，返回
        return $this->success([
            "addressId"             => $addressId,
            "couponId"              => $couponId,
            "userCouponId"          => $userCouponId,
            "cartId"                => $cartId,
            "grouponRulesId"        => $groupRulesId,
            "grouponPrice"          => $grouponPrice,
            "checkedAddress"        => $address,
            "availableCouponLength" => $countUserCoupon,
            "goodsTotalPrice"       => $checkedGoodsPrice,
            "freightPrice"          => (int) $freightPrice,
            "couponPrice"           => $couponPrice,
            "orderTotalPrice"       => $orderPrice,
            "actualPrice"           => $orderPrice,
            "checkedGoodsList"      => $checkedGoodsList->toArray(),
        ]);
    }
}
