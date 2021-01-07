<?php


namespace App\Http\Controllers\Wx;


use App\Http\Controllers\Controller;
use App\util\CodeResponse;
use App\util\ValidateRequest;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class WxController extends Controller
{
    use ValidateRequest;

    /**
     * @param $codeResponse
     * @param null $data
     * @param string $info
     * @return JsonResponse
     */
    protected function codeReturn($codeResponse, $data = null, $info = ''): JsonResponse
    {
        list($errno, $errmsg) = $codeResponse;
        $res = ['errno'=>$errno, 'errmsg'=> $info ?: $errmsg];
        if(!is_null($data)) {
            $res['data'] = $data;
        }
        return response()->json($res);
    }

    /**
     * @param array $data
     * @return JsonResponse
     */
    protected function success($data = []): JsonResponse
    {
        return $this->codeReturn(CodeResponse::SUCCESS, $data);
    }

    /**
     * @param array $codeResponse
     * @param string $info
     * @return JsonResponse
     */
    protected function fail($codeResponse = CodeResponse::FAIL, $info=''): JsonResponse
    {
        return $this->codeReturn($codeResponse, null, $info);
    }

    /**
     * @param $isSuccess
     * @param array $codeResponse
     * @param array $data
     * @param string $info
     * @return JsonResponse
     */
    protected function failOrSuccess($isSuccess, $codeResponse=CodeResponse::FAIL, $data = [], $info=''): JsonResponse
    {
        if($isSuccess) {
            return $this->success($data);
        }
        return $this->fail($codeResponse, $info);
    }

    /**
     * @return Authenticatable|null
     */
    public function user(): ?Authenticatable
    {
        return Auth::guard('wx')->user();
    }

    /**
     * @return mixed
     */
    public function userId()
    {
        return $this->user()->getAuthIdentifier();
    }
}
