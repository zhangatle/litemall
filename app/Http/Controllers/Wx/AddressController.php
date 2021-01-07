<?php

namespace App\Http\Controllers\Wx;

use App\Exceptions\BusinessException;
use App\Http\Requests\AddressSaveRequest;
use App\Services\AddressService;
use App\util\CodeResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends WxController
{
    public function list(Request $request): JsonResponse
    {
        $list = AddressService::getAddressByUserId($this->userId());
        return $this->success([
            'total' => $list->count(),
            'page' => 1,
            'list' => $list->toArray(),
            'pages' => 1
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws BusinessException
     */
    public function detail(Request $request): JsonResponse
    {
        $id = $this->verifyId('id', 0);
        $address = AddressService::getUserAddress($this->userId(), $id);
        if(is_null($address)) {
            return $this->fail(CodeResponse::INVALID_PARAM);
        }
        return $this->success($address);
    }

    /**
     * @param AddressSaveRequest $request
     * @return JsonResponse
     * @throws BusinessException
     */
    public function save(AddressSaveRequest $request): JsonResponse
    {
        $userId = $this->userId();
        AddressService::saveAddress($userId, $request);
        return $this->success();
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws BusinessException
     */
    public function delete(Request $request): JsonResponse
    {
        $id = $this->verifyInteger('id');
        AddressService::delete($this->userId(), $id);
        return $this->success();
    }
}
