<?php


namespace App\Services;


use App\Models\SearchHistory;

class SearchHistoryService extends BaseService
{
    /**
     * @param $userId
     * @param $keyword
     * @param $from
     * @return bool
     */
    public function save($userId, $keyword, $from): bool
    {
        $history = new SearchHistory();
        $history->user_id = $userId;
        $history->keyword = $keyword;
        $history->from = $from;
        return $history->save();
    }
}
