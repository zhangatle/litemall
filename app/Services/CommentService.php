<?php


namespace App\Services;


use App\Models\Comment;
use App\util\Constant;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class CommentService extends BaseService
{
    /**
     * @param $goods_id
     * @param int $page
     * @param int $limit
     * @param string $sort
     * @param string $order
     * @return LengthAwarePaginator
     */
    public function getGoodsComment($goods_id, $page = 1, $limit = 2, $sort='created_at', $order = 'desc'): LengthAwarePaginator
    {
        return Comment::query()->where('value_id', $goods_id)->where('type', Constant::COLLECT_GOOD_TYPE)->orderBy($sort, $order)->paginate($limit, ['*'], 'page', $page);
    }


    /**
     * @param $goodsId
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getGoodsCommentWithUserInfo($goodsId, $page = 1, $limit= 2): array
    {
        $comment = $this->getGoodsComment($goodsId, $page, $limit);
        $userIds = Arr::pluck($comment->items(), 'user_id');
        $userIds = array_unique($userIds);
        $users = UserService::getInstance()->getUsers($userIds)->keyBy('id');
        $data = collect(($comment->items()))->map(function ($comment) use ($users) {
            $user = $users->get($comment->user_id);
            return [
                'id'           => $comment->id,
                'addTime'      => Carbon::instance($comment->add_time)->toDateTimeString(),
                'content'      => $comment->content,
                'adminContent' => $comment->admin_content,
                'picList'      => $comment->pic_urls,
                'nickname'     => $user->nickname ?? '',
                'avatar'       => $user->avatar ?? ''
            ];
        });
        return ['count'=>$comment->total(), 'data'=>$data];
    }
}
