<?php


namespace App\Services;


use App\Http\Requests\PageRequest;
use App\Models\Collect;
use App\util\Constant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CollectService extends BaseService
{
    /**
     * @param $goodIds
     * @return int
     */
    public function getGoodsCollect($goodIds): int
    {
        return Collect::query()->where('type', Constant::COLLECT_GOOD_TYPE)->where('value_id', $goodIds)->count('id');
    }

    /**
     * @param PageRequest $request
     * @param $userId
     * @param string[] $columns
     * @return LengthAwarePaginator
     */
    public function getList(PageRequest $request, $userId, $columns = ['*']): LengthAwarePaginator
    {
        return Collect::query()->whereUserId($userId)->paginate($request->limit, $columns, 'page', $request->page);
    }

    /**
     * @param $userId
     * @param $type
     * @param $valueId
     * @return bool|mixed
     * @throws \Exception
     */
    public function addOrDelete($userId, $type, $valueId): bool
    {
        $where = [
            'user_id' => $userId,
            'type' => $type,
            'value_id' => $valueId
        ];
        $collect = Collect::query()->where($where)->get()->toArray();
        if (!empty($collect)) {
            return Collect::query()->where($where)->delete();
        } else {
            $collect = new Collect();
            $collect->type = $type;
            $collect->value_id = $valueId;
            $collect->user_id = $userId;
            return $collect->save();
        }
    }
}
