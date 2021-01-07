<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Exception;

class BaseModel extends Model
{
    use SoftDeletes;

    public function cas()
    {
        //当数据不存在时，禁止更新操作
        try {
            throw_if(!$this->exists, Exception::class, ['the data is not exist']);
        } catch (\Throwable $e) {
            Log::info($e->getMessage());
        }

        //当内存中更新数据为空时，禁止更新操作
        $dirty = $this->getDirty(); //内存中修改的值
        if (empty($dirty)) {
            return 0;
        }

        //当模型开启自动更新时间字段时，附上更新的时间字段
        if ($this->usesTimestamps()) {
            $this->updateTimestamps();
            $dirty = $this->getDirty();
        }

        $diff = array_diff(array_keys($dirty), array_keys($this->getOriginal()));

        if ($this->fireModelEvent('casing') === false) {
            return 0;
        }

        try {
            throw_if(!empty($diff), Exception::class, ['key [ ' . implode(',', $diff) . ' ] is not exist']);
        } catch (\Throwable $e) {
            Log::info($e->getMessage());
        }

        //使用newModelQuery 更新的时候不用带上 deleted = 0 的条件
        $query = self::newModelQuery()->where($this->getKeyName(), $this->getKey());

        foreach ($dirty as $k => $v) {
            $query = $query->where($k, $this->getOriginal($k));  //判断一下更新的字段值是否有改动
        }

        $row = $query->update($dirty);
        if ($row > 0) {
            $this->syncChanges();
            $this->fireModelEvent('cased', false);
            $this->syncOriginal();
        }
        return $row;
    }
}
