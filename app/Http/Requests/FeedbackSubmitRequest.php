<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FeedbackSubmitRequest extends FormRequest
{
    public $mobile;
    public $feedType;
    public $content;
    public $status = 1;
    public $hasPicture = 0;
    public $pic_urls = '';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'mobile'   => 'required|regex:/^1[0-9]{10}$/',
            'feedType' => 'required|string',
            'content'  => 'required|string',
        ];
    }
}
