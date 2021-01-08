<?php

namespace App\Http\Controllers\Wx;


use App\Exceptions\BusinessException;
use App\Http\Requests\PageRequest;
use App\Services\BrandService;
use App\util\CodeResponse;
use GuzzleHttp\Psr7\Request;
use Illuminate\Http\JsonResponse;

class BrandController extends WxController
{
    /**
     * @param PageRequest $request
     * @return JsonResponse
     */
    public function list(PageRequest $request): JsonResponse
    {
        $list = BrandService::getInstance()->getBrandList($request, ['id', 'name', 'desc', 'pic_url', 'floor_price']);
        return $this->successPaginate($list);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws BusinessException
     */
    public function detail(Request $request): JsonResponse
    {
        $id = $this->verifyId('id');
        $brand = BrandService::getInstance()->getBrand($id);
        if(is_null($brand)) {
            return $this->fail(CodeResponse::PARAM_NOT_EMPTY, '数据不存在');
        }
        return $this->success($brand->toArray());
    }
}
