<?php


namespace App\Services;


use App\Exceptions\BusinessException;
use App\Http\Requests\AddressSaveRequest;
use App\Models\Address;
use App\util\CodeResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class AddressService extends BaseService
{
    /**
     * 根据用户ID获取用户
     * @param $userId
     * @return Address[]|Builder[]|Collection
     */
    public function getAddressByUserId($userId)
    {
        return Address::query()->whereUserId($userId)->get();
    }

    /**
     * 重置用户默认收货地址
     * @throws BusinessException
     */
    public function resetDefaultAddress(): bool
    {
        if (!Address::query()->update(["is_default" => 0])) {
            $this->throwBusinessException(CodeResponse::UPDATED_FAIL);
        }
        return true;
    }

    /**
     * 保存用户收货地址
     * @param $userId
     * @param AddressSaveRequest $request
     * @return Address
     * @throws BusinessException
     */
    public  function saveAddress($userId, AddressSaveRequest $request): Address
    {
        if (!is_null($request->id)) {
            $address = $this->getUserAddress($userId, $request->id);
        } else {
            $address = new Address();
            $address->user_id = $userId;
        }
        if ($request->isDefault) {
            $this->resetDefaultAddress();
        }
        $address->address_detail = $request->addressDetail;
        $address->area_code = $request->areaCode;
        $address->city = $request->city;
        $address->country = $request->country;
        $address->is_default = $request->isDefault;
        $address->name = $request->name;
        $address->postal_code = $request->postalCode;
        $address->province = $request->province;
        $address->tel = $request->tel;
        $address->save();
        return $address;
    }

    /**
     * 删除用户收货地址
     * @param $userId
     * @param $id
     * @throws BusinessException
     * @throws \Exception
     */
    public  function delete($userId, $id)
    {
        $address = $this->getUserAddress($userId, $id);
        if(is_null($address)) {
            $this->throwBusinessException();
        }
        Address::query()->where('id', $id)->where('user_id', $userId)->delete();
    }

    /**
     * 获取用户地址
     * @param $userId
     * @param $id
     * @return Address|Builder
     */
    public  function getUserAddress($userId, $id) {
        return Address::query()->whereUserId($userId)->whereId($id);
    }

    /**
     * 获取用户收货地址
     * @param $userId
     * @param null $addressId
     * @return Address|Builder|Model|object|null
     * @throws BusinessException
     */
    public function getAddressOrDefault($userId, $addressId = null) {
        if (empty($addressId)) {
            $address = $this->getDefaultAddress($userId);
        }else {
            $address = $this->getUserAddress($userId, $addressId);
            if (empty($address)) {
                $this->throwBusinessException(CodeResponse::SYSTEM_ERROR);
            }
        }
        return $address;
    }

    /**
     * 获取用户默认收货地址
     * @param $userId
     * @return Address|Builder|Model|object|null
     */
    public  function getDefaultAddress($userId) {
        return Address::query()->whereUserId($userId)->where('is_default', 1)->first();
    }
}
