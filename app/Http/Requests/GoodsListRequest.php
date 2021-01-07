<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GoodsListRequest extends FormRequest
{
    public $categoryId;
    public $brandId;
    public $keyword;
    public $isNew;
    public $isHot;
    public $page = 1;
    public $limit = 10;
    public $sort = 'created_at';
    public $order = 'desc';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'categoryId' => 'integer',
            'brandId'    => 'integer',
            'keyword'    => 'string',
            'isNew'      => 'boolean',
            'isHot'      => 'boolean',
            'page'       => 'integer',
            'limit'      => 'integer',
            'sort'       => Rule::in(['created_at', 'retail_price', 'name']),
            'order'      => Rule::in(['desc', 'asc'])
        ];
    }
}
