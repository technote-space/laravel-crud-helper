<?php
declare(strict_types=1);

namespace Technote\CrudHelper\Tests;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Technote\CrudHelper\Models\Contracts\Crudable as CrudableContract;
use Technote\CrudHelper\Models\Traits\Crudable;

/**
 * Class Item
 * @package Technote\CrudHelper\Tests
 * @mixin Eloquent
 */
class Item extends Model implements CrudableContract
{
    use Crudable;

    /**
     * @var array
     */
    protected $guarded = [
        'id',
    ];
}
