<?php


namespace App\Services;


use App\Http\Requests\PageRequest;
use App\Models\Issue;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class IssueService extends BaseService
{
    /**
     * @param PageRequest $request
     * @param string[] $column
     * @return LengthAwarePaginator
     */
    public function getList(PageRequest $request, $column= ['*']): LengthAwarePaginator
    {
        return Issue::query()->paginate($request->limit, $column, 'page', $request->page);
    }
}
