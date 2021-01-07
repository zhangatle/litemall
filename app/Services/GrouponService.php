<?php


namespace App\Services;


use App\Exceptions\BusinessException;
use App\Http\Requests\PageRequest;
use App\Models\Groupon;
use App\Models\GrouponRules;
use App\util\CodeResponse;
use App\util\Constant;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use SimpleSoftwareIO\QrCode\Image;

class GrouponService extends BaseService
{
    public function getGrouponListByLimit($limit = 5, $offset = 0, $order = 'desc', $sort = 'add_time')
    {
        return Groupon::query()->offset($offset)->limit($limit)->orderBy($sort, $order)->get();
    }

    /**
     * @param $orderIds
     * @return array
     * 获取所有有团购的订单ID
     */
    public function getGrouponOrderByOrderIds($orderIds): array
    {
        return Groupon::query()->whereIn('order_id', $orderIds)->pluck('order_id')->toArray();
    }

    /**
     * @param $orderId
     * @throws BusinessException
     * 支付团购订单
     */
    public function payGrouponOrder($orderId)
    {
        $groupon = $this->getGrouponByOrderId($orderId);

        if (is_null($groupon)) {
            return;
        }

        $rule = $this->getGrouponRuleById($groupon->rules_id);

        if ($groupon->groupon_id == 0) {
            $groupon->share_url = $this->createGroupShareImage($rule);
        }

        $groupon->status = Constant::Groupon_STATUS_ON;
        $isSuccess = $groupon->save();

        if (!$isSuccess) {
            $this->throwBusinessException(CodeResponse::UPDATED_FAIL);
        }

        $joinCount = $this->countGrouponJoin($groupon->groupon_id);

        //小于开团人数，直接返回
        if ($joinCount < $rule->discount_member - 1) {
            return;
        }

        //达到开团人数，修改团购表为团购成功，然后安排发货等操作
        $row = Groupon::query()->where(function (Builder $builder) use ($groupon) {
            return $builder->where('groupon_id', $groupon->groupon_id)
                ->orWhere('id', $groupon->id);
        })->update(['status' => Constant::Groupon_STATUS_SUCCEED]);

        if ($row == 0) {
            $this->throwBusinessException(CodeResponse::UPDATED_FAIL);
        }
    }

    /**
     * 创建团购分享图片
     * 1、获取链接，创建二维码
     * 2、合成图片
     * 3、保存图片，返回图片地址
     * @param GrouponRules $rules
     * @return string
     */
    public function createGroupShareImage(GrouponRules $rules): string
    {
        $shareUrl = \route('home.redirectShareUrl', ['type' => 'groupon', 'id' => $rules->goods_id]);
        $qrcode = QrCode::format('png')->margin(1)->size(290)->generate($shareUrl);
        $goodsImage = Image::make($rules->pic_url)->resize(660, 660);

        $image = Image::make(resource_path('/images/back_groupon.png'))
            ->insert($qrcode, 'top-left', 460, 770)
            ->insert($goodsImage, 'top-left', 71, 69)
            ->text($rules->goods_name, 65, 867, function (AbstractFont $font) {
                $font->color(array(167, 136, 69));
                $font->file(resource_path('ttf/msyh.ttf'));
                $font->size(28);
            });

        $filePath = 'groupon/' . Carbon::now()->toDateString() . '/' . Str::random() . '.png';
        Storage::disk('public')->put($filePath, $image->encode());
        return Storage::url($filePath);
    }

    /**
     * @param $orderId
     * @param string[] $column
     * @return Groupon|Builder|Model|object|null
     * 根据订单Id获取团购数据
     */
    public function getGrouponByOrderId($orderId, $column = ['*'])
    {
        return Groupon::query()->whereOrderId($orderId)->first($column);
    }


    /**
     * @param PageRequest $request
     * @param string[] $column
     * @return LengthAwarePaginator
     * 获取团购规则列表数据
     */
    public function getGroupRuleLists(PageRequest $request, $column = ['*']): LengthAwarePaginator
    {
        return GrouponRules::query()->where('status', Constant::Groupon_RULE_STATUS_ON)->orderBy($request->sort,
            $request->order)->paginate($request->limit, $column, 'page', $request->page);
    }

    /**
     * @param $ruleId
     * @param string[] $column
     * @return GrouponRules|GrouponRules[]|Builder|Builder[]|Collection|Model|null
     * 获取团购规则的数据
     */
    public function getGrouponRuleById($ruleId, $column = ['*'])
    {
        return GrouponRules::query()->find($ruleId, $column);
    }

    /**
     * @param $grouponId
     * @return int
     * 获取参团的人数
     */
    public function countGrouponJoin($grouponId): int
    {
        return Groupon::query()->whereGrouponId($grouponId)->where('status', '!=', Constant::Groupon_STATUS_NONE)->count(['id']);
    }

    /**
     * @param $userId
     * @param $grouponId
     * @return bool
     * 判断这个用户是否已经参团或者开团
     */
    public function isOpenOrJoin($userId, $grouponId): bool
    {
        return Groupon::query()->whereUserId($userId)->where(function (Builder $builder) use ($grouponId) {
            return $builder->where('groupon_id', $grouponId)->orWhere('id', $grouponId);
        })->where('status', '!=', Constant::Groupon_STATUS_NONE)->exists();
    }

    /**
     * @param $userId
     * @param $ruleId
     * @param null $linkId
     * @throws BusinessException
     * 检查用户是否有开团的资格
     */
    public function checkGrouponRulesValid($userId, $ruleId, $linkId = null)
    {
        if ($ruleId == null || $ruleId < 0) {
            return;
        }
        $grouponRule = $this->getGrouponRuleById($ruleId);
        if (is_null($grouponRule)) {
            $this->throwBusinessException(CodeResponse::PARAM_NOT_EMPTY);
        }
        if ($grouponRule->status == Constant::Groupon_RULE_STATUS_DOWN_EXPIRE) {
            $this->throwBusinessException(CodeResponse::GROUPON_EXPIRED);
        }
        if ($grouponRule->status == Constant::Groupon_RULE_STATUS_DOWN_ADMIN) {
            $this->throwBusinessException(CodeResponse::GROUPON_OFFLINE);
        }

        if ($linkId == null || $linkId < 0) {
            return;
        }

        if ($this->countGrouponJoin($linkId) >= $grouponRule->discount_member) {
            $this->throwBusinessException(CodeResponse::GROUPON_FULL);
        }

        if ($this->isOpenOrJoin($userId, $linkId)) {
            $this->throwBusinessException(CodeResponse::GROUPON_JOIN);
        }
    }

    /**
     * @param int $groupId 团购ID
     * @param string[] $column 字段
     * @return Groupon|Groupon[]|Builder|Builder[]|Collection|Model|null
     * 获取团购的数据
     */
    public function getGrouponById(int $groupId, $column = ['*'])
    {
        return Groupon::query()->find($groupId, $column);
    }

    /**
     * @param $ruleId
     * @param $userId
     * @param $orderId
     * @param $linkId
     * @return bool
     * 保存团购相关的数据
     */
    public function saveGrouponData($ruleId, $userId, $orderId, $linkId = null): bool
    {
        if ($ruleId == null || $ruleId < 0) {
            return $ruleId;
        }
        $groupon = new Groupon();
        $groupon->order_id = $orderId;
        $groupon->status = Constant::Groupon_STATUS_NONE;
        $groupon->user_id = $userId;
        $groupon->rules_id = $ruleId;

        //参与者
        if ($linkId != null && $linkId > 0) {
            $groupon->groupon_id = $linkId;
            $baseGroupon = $this->getGrouponById($linkId);
            $groupon->creator_user_id = $baseGroupon->creator_user_id;
            $groupon->share_url = $baseGroupon->share_url;
            $groupon->save();
        }
        $groupon->creator_user_id = $userId;
        $groupon->groupon_id = 0;
        $groupon->creator_user_time = Carbon::now()->toDateTimeString();
        return $groupon->save();
    }
}
