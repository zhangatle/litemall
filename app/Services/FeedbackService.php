<?php


namespace App\Services;


use App\Exceptions\BusinessException;
use App\Http\Requests\FeedbackSubmitRequest;
use App\Models\Feedback;
use App\util\CodeResponse;

class FeedbackService extends BaseService
{
    /**
     * @param FeedbackSubmitRequest $request
     * @param $userId
     * @return bool
     * @throws BusinessException
     */
    public function add(FeedbackSubmitRequest $request, $userId): bool
    {
        $user = UserService::getInstance()->getUserById($userId);
        $feedback = new Feedback();
        $feedback->status = $request->status;
        $feedback->content = $request->content;
        $feedback->mobile = $request->mobile;
        $feedback->user_id = $userId;
        $feedback->username = $user->username;
        $feedback->feed_type = $request->feedType;
        if(!$feedback->save()) {
            $this->throwBusinessException(CodeResponse::UPDATED_FAIL);
        }
        return true;
    }
}
