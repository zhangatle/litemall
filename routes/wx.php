<?php

use Illuminate\Support\Facades\Route;

Route::post('auth/register', 'AuthController@register'); //账号注册
Route::post('auth/regCaptcha', 'AuthController@regCaptcha'); //注册验证码
Route::post('auth/login', 'AuthController@login'); //账号登录

Route::middleware(['auth:wx'])->group(function () {
    //Route::any('/auth/login_by_weixin',''); //微信登录
    Route::post('/auth/logout','AuthController@logout'); //账号登出
    Route::get('/auth/info','AuthController@info'); //用户信息
    Route::post('/auth/profile','AuthController@profile'); //账号修改
    Route::post('/auth/reset','AuthController@reset'); //账号密码重置
    Route::post('/auth/captcha','AuthController@regCaptcha'); //验证码

    Route::get('/address/list','AddressController@list'); //收货地址列表
    Route::get('/address/detail','AddressController@detail'); //收货地址详情
    Route::post('/address/save','AddressController@save'); //保存收货地址
    Route::post('/address/delete','AddressController@delete'); //删除收货地址
});


//Route::any('/home/index',''); //首页数据接口
//Route::any('/catalog/index',''); //分类目录全部分类数据接口
//Route::any('/catalog/current',''); //分类目录当前分类数据接口
//Route::any('/goods/count',''); //统计商品总数
//Route::any('/goods/list',''); //获得商品列表
//Route::any('/goods/category',''); //获得分类数据
//Route::any('/goods/detail',''); //获得商品的详情
//Route::any('/goods/related',''); //商品详情页的关联商品（大家都在看）
//Route::any('/brand/list',''); //品牌列表
//Route::any('/brand/detail',''); //品牌详情
//Route::any('/cart/index',''); //获取购物车的数据
//Route::any('/cart/add',''); // 添加商品到购物车
//Route::any('/cart/fastadd',''); // 立即购买商品
//Route::any('/cart/update',''); // 更新购物车的商品
//Route::any('/cart/delete',''); // 删除购物车的商品
//Route::any('/cart/checked',''); // 选择或取消选择商品
//Route::any('/cart/goodscount',''); // 获取购物车商品件数
//Route::any('/cart/checkout',''); // 下单前信息确认
//Route::any('/collect/list',''); //收藏列表
//Route::any('/collect/addordelete',''); //添加或取消收藏
//Route::any('/comment/list',''); //评论列表
//Route::any('/comment/count',''); //评论总数
//Route::any('/comment/post',''); //发表评论
//Route::any('/topic/list',''); //专题列表
//Route::any('/topic/detail',''); //专题详情
//Route::any('/topic/related',''); //相关专题
//Route::any('/search/index',''); //搜索关键字
//Route::any('/search/result',''); //搜索结果
//Route::any('/search/helper',''); //搜索帮助
//Route::any('/search/clearhistory',''); //搜索历史清楚

//Route::any('/express/query',''); //物流查询
//Route::any('/order/submit',''); // 提交订单
//Route::any('/order/prepay',''); // 订单的预支付会话
//Route::any('/order/h5pay',''); // h5支付
//Route::any('/order/list',''); //订单列表
//Route::any('/order/detail',''); //订单详情
//Route::any('/order/cancel',''); //取消订单
//Route::any('/order/refund',''); //退款取消订单
//Route::any('/order/delete',''); //删除订单
//Route::any('/order/confirm',''); //确认收货
//Route::any('/order/goods',''); // 代评价商品信息
//Route::any('/order/comment',''); // 评价订单商品信息
//Route::any('/feedback/submit',''); //添加反馈
//Route::any('/footprint/list',''); //足迹列表
//Route::any('/footprint/delete',''); //删除足迹
//Route::any('/groupon/list',''); //团购列表
//Route::any('/groupon/query',''); //团购API-查询
//Route::any('/groupon/my',''); //团购API-我的团购
//Route::any('/groupon/detail',''); //团购API-详情
//Route::any('/groupon/join',''); //团购API-详情
//Route::any('/coupon/list',''); //优惠券列表
//Route::any('/coupon/mylist',''); //我的优惠券列表
//Route::any('/coupon/selectlist',''); //当前订单可用优惠券列表
//Route::any('/coupon/receive',''); //优惠券领取
//Route::any('/coupon/exchange',''); //优惠券兑换
//Route::any('/storage/upload',''); //图片上传,
//Route::any('/user/index',''); //个人页面用户相关信息
//Route::any('/issue/list',''); //帮助信息
