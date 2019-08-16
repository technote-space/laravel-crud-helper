<?php
declare(strict_types=1);

namespace Technote\CrudHelper\Tests;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class UserDetail
 * @package Technote\CrudHelper\Tests
 * @mixin Eloquent
 */
class UserDetail extends Model
{
    /**
     * @var array
     */
    protected $guarded = [
        'id',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'test_id' => 'int',
    ];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
