<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    private static $defaultPageSize = 10;

    protected $guarded = [];
    protected $dates = ['date'];

    public static function getDefaultPageSize()
    {
        return self::$defaultPageSize;
    }

    public static function getCustomerList($pageSize = null, $keyword = null)
    {
        if (!isset($pageSize)) {
            $pageSize = self::$defaultPageSize;
        }

        return Customer::where('name', 'LIKE', "%$keyword%")
            ->paginate($pageSize);
    }
}
