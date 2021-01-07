<?php


namespace App\Services;


use App\Http\Requests\PageRequest;
use App\Models\Brand;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class BrandService extends BaseService
{
    public function getBrandByLimit($limit, $columns = ['*'], $offset = 0)
    {
        return Brand::query()->offset($offset)->limit($limit)->get($columns);
    }

    /**
     * @param $id
     * 获取品牌的详细数据
     * @return Builder|Builder[]|Collection|Model|null
     */
    public function getBrand($id)
    {
        return Brand::query()->find($id);
    }

    /**
     * @param PageRequest $request
     * @param string[] $columns
     * @return LengthAwarePaginator
     */
    public function getBrandList(PageRequest $request, $columns = ['*']): LengthAwarePaginator
    {
        return  Brand::query()->orderBy($request->sort, $request->order)->paginate($request->limit, $columns, 'page', $request->page);
    }
}
