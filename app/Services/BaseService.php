<?php


namespace App\Services;


use App\Exceptions\BusinessException;
use App\util\CodeResponse;

class BaseService
{
    protected static $instance = [];

    private function __construct()
    {
    }

    private function __clone() {

    }

    /**
     * 单例模式
     * @return static
     */
    public static function getInstance(): BaseService
    {
        if((static::$instance[static::class] ?? []) instanceof static) {
            return static::$instance[static::class];
        }
        static::$instance[static::class] = new static();
        return static::$instance[static::class];
    }



    /**
     * @param array $response
     * @param null $info
     * @throws BusinessException
     */
    public function throwBusinessException(array $response = CodeResponse::INVALID_PARAM, $info = null)
    {
        if (!is_null($info)) {
            $response[1] = $info;
        }
        throw new BusinessException($response);
    }
}
