<?php


namespace App\Services;


use App\Http\Requests\PageRequest;
use App\Models\Topic;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class TopicService extends BaseService
{
    /**
     * @param $limit
     * @param int $offset
     * @param string $order
     * @param string $sort
     * @return Builder[]|Collection
     */
    public function getTopicByLimit($limit, $offset = 0, $order = 'desc', $sort = 'add_time')
    {
        return Topic::query()->orderBy($sort, $order)->offset($offset)->limit($limit)->get();
    }

    /**
     * @param PageRequest $request
     * @param string[] $columns
     * @return LengthAwarePaginator
     */
    public function getList(PageRequest $request, $columns = ['*']): LengthAwarePaginator
    {
        return Topic::query()->paginate($request->limit, $columns, 'page', $request->page);
    }

    /**
     * @param $id
     * @return array
     */
    public function getDetail($id): array
    {
        $topic = $goods = [];
        /** @var Topic $topic */
        $topic = Topic::query()->whereId($id)->first();
        if (empty($topic)) {
            return array($topic, $goods);
        }
        $goodIds = json_decode($topic->goods, true);
        if (!empty($goodIds)) {
            $goods = GoodsService::getInstance()->getGoodsListByIds($goodIds)->toArray();
        }
        return array($topic, $goods);
    }

    /**
     * @param $id
     * @return Topic[]|array|BuildsQueries[]|Builder[]|Collection|Builder[]|\Illuminate\Support\Collection
     */
    public function getRelated($id)
    {
        $topic = Topic::query()->whereId($id)->first();
        return Topic::query()->when(!empty($topic), function (Builder $builder) use ($topic) {
            return $builder->whereNotIn('id', array($topic->id));
        })->offset(0)->limit(4)->orderBy('add_time', 'desc')->get();
    }
}
