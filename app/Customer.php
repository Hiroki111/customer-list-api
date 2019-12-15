<?php

namespace App;

use App\Group;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    private static $defaultPageSize = 10;

    protected $guarded = [];
    protected $dates = ['date'];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public static function getDefaultPageSize()
    {
        return self::$defaultPageSize;
    }

    public static function getCustomerList($pageSize = null, $keyword = null)
    {
        if (!isset($pageSize)) {
            $pageSize = self::$defaultPageSize;
        }

        return Customer::with('group')
            ->where('name', 'LIKE', "%$keyword%")
            ->paginate($pageSize);
    }
}
