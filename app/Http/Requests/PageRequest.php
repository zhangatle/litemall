<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PageRequest extends FormRequest
{
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
            'page'  => 'integer',
            'limit' => 'integer',
            'sort'  => 'string',
            'order' => Rule::in(['desc', 'asc'])
        ];
    }
}
