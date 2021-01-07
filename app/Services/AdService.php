<?php


namespace App\Services;


use App\Models\Ad;

class AdService extends BaseService
{
    public function queryIndex() {
        return Ad::query()->wherePosition(1)->whereEnabled(1)->get();
    }
}
