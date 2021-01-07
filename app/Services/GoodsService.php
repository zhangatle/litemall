<?php


namespace App\Services;


use App\Http\Requests\GoodsListRequest;
use App\Models\Footprint;
use App\Models\Goods;
use App\Models\GoodsAttribute;
use App\Models\GoodsProduct;
use App\Models\GoodsSpecification;
use App\Models\Issue;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Throwable;

class GoodsService extends BaseService
{
    /**
     * @param $offset
     * @param $limit
     * 获取新发商品
     * @return Builder[]|Collection|Goods[]
     */
    public function getNewGoods($limit, $offset = 0)
    {
        $conditions = [
            'is_on_sale' => 1,
            'is_new'     => 1
        ];
        return $this->getGoodsByConditions($conditions, $offset, $limit);
    }

    /**
     * @param $offset
     * @param $limit
     * @return Goods[]|Builder[]|Collection
     * 获取热门商品
     */
    public function getHotGoods($limit, $offset = 0)
    {
        $conditions = [
            'is_hot'     => 1,
            'is_on_sale' => 1
        ];
        return $this->getGoodsByConditions($conditions, $offset, $limit);
    }

    /**
     * @param $conditions
     * @param $offset
     * @param $limit
     * @param  string  $sort
     * @param  string  $order
     * @param  string[]  $columns
     * @return Goods[]|Builder[]|Collection
     * 根据条件获取商品数据
     */
    private function getGoodsByConditions(
        $conditions,
        $offset,
        $limit,
        $sort = 'add_time',
        $order = 'desc',
        $columns = ['id', 'name', 'brief', 'pic_url', 'is_new', 'is_hot', 'counter_price', 'retail_price']
    ) {
        return Goods::query()->where($conditions)->offset($offset)->limit($limit)->orderBy($sort, $order)->get($columns);
    }

    /**
     * @param $productId
     * @param $num
     * @return int
     * 减库存
     */
    public function reduceStock($productId, $num): int
    {
        return GoodsProduct::query()->where('id', $productId)->where('number', '>=', $num)->decrement('number', $num);
    }

    /**
     * @param $productId
     * @param $num
     * @return int
     * 加库存 使用乐观锁
     * @throws Throwable
     */
    public function addStock($productId, $num): int
    {
        /** @var GoodsProduct $product */
        $product         = $this->getGoodsProductById($productId);
        $product->number = $product->number + $num;
        return $product->cas();
    }


    /**
     * @param $ids
     * @return Goods[]|Builder[]|Collection
     * 根据商品的id,获取商品的列表
     */
    public function getGoodsListByIds($ids)
    {
        return Goods::query()->whereIn('id', $ids)->get();
    }

    /**
     * @param $userId
     * @param $goodId
     * 记录用户的足迹
     */
    public function saveFootPrint($userId, $goodId)
    {
        $footPrint              = new FootPrint();
        $footPrint->goods_id    = $goodId;
        $footPrint->user_id     = $userId;
        $footPrint->update_time = Carbon::now()->toDateTimeString();
        $footPrint->deleted     = 0;
        $footPrint->save();
    }

    /**
     * @return Builder[]|Collection
     * 获取商品的问题
     */
    public function getGoodsIssue()
    {
        return Issue::query()->get();
    }

    /**
     * @param $id
     * @return Builder[]|Collection
     * 获取商品的产品
     */
    public function getGoodsProducts($id)
    {
        return GoodsProduct::query()->where('goods_id', $id)->get();
    }

    /**
     * @param $id
     * @return GoodsProduct|GoodsProduct[]|Builder|Builder[]|Collection|Model|null
     * 根据产品的ID获取产品信息
     */
    public function getGoodsProductById($id)
    {
        return GoodsProduct::query()->find($id);
    }

    /**
     * @param  array  $ids
     * @return GoodsProduct[]|Builder[]|Collection
     * 批量获取产品
     */
    public function getGoodsProductsByIds(array $ids)
    {
        return GoodsProduct::query()->whereIn('id', $ids)->get();
    }

    /**
     * @param $id
     * @return Builder[]|Collection
     * 获取产品的规格
     */
    public function getGoodsSpecification($id)
    {
        $spec = GoodsSpecification::query()->where('goods_id', $id)->get();
        $spec = $spec->groupBy('specification');
        return $spec->map(function ($v, $k) {
            return ['name' => $k, 'valueList' => $v];
        })->values();
    }

    public function getGoodsAttributesList($id)
    {
        return GoodsAttribute::query()->where('goods_id', $id)->get();
    }

    /**
     * @param $id
     * @return Builder|Builder[]|Collection|Model|null|Goods
     * 获取商品
     */
    public function getGoods($id)
    {
        return Goods::query()->find($id);
    }

    /**
     * @return int
     * 获取在售商品的数量
     */
    public function countGoodsOnSales(): int
    {
        return Goods::query()->where('is_on_sale', 1)->count('id');
    }

    /**
     * @param GoodsListRequest $request
     * @return mixed
     * 获取商品的列表
     */
    public function GoodsLists(GoodsListRequest $request)
    {

        $query = Goods::query()->select([
            'id', 'name', 'brief', 'pic_url', 'is_new', 'is_hot', 'counter_price', 'retail_price'
        ])->where('is_on_sale', 1);

        $query = $this->getGoodsQuery($query, $request->keyword, $request->brandId, $request->isNew, $request->isHot);

        if (!empty($request->categoryId)) {
            $query = $query->where('category_id', $request->categoryId);
        }

        if (!empty($request->sort) && !empty($request->order)) {
            $query = $query->orderBy($request->sort, $request->order);
        }

        return $query->paginate($request->limit, ['*'], 'page', $request->page);
    }

    /**
     * @param GoodsListRequest $request
     * @return mixed
     * 获取商品分类ID的数据
     */
    public function getCatIds(GoodsListRequest $request)
    {
        $query = Goods::query()->where('is_on_sale', 1);
        $query = $this->getGoodsQuery($query, $request->keyword, $request->brandId, $request->isNew, $request->isHot);
        return $query->select(['category_id'])->pluck('category_id')->toArray();
    }

    private function getGoodsQuery($query, $keywords, $brandId, $isNew, $isHot)
    {
        if (!empty($brandId)) {
            $query = $query->where('brand_id', $brandId);
        }

        if (!is_null($isNew)) {
            $query = $query->where('is_new', $isNew);
        }

        if (!is_null($isHot)) {
            $query = $query->where('is_hot', $isHot);
        }

        if (!empty($keywords)) {
            $query->Where('keywords', 'like', "%{$keywords}%")->orWhere('name', 'like', "%{$keywords}%");
        }
        return $query;
    }
}
