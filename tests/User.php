<?php
declare(strict_types=1);

namespace Technote\CrudHelper\Tests;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Technote\CrudHelper\Models\Contracts\Crudable as CrudableContract;
use Technote\CrudHelper\Models\Traits\Crudable;
use Technote\SearchHelper\Models\Contracts\Searchable as SearchableContract;
use Technote\SearchHelper\Models\Traits\Searchable;

/**
 * Class User
 * @package Technote\CrudHelper\Tests
 * @mixin Eloquent
 */
class User extends Model implements CrudableContract, SearchableContract
{
    use Crudable, Searchable;

    /**
     * @var array
     */
    protected $guarded = [
        'id',
    ];

    /**
     * @var array
     */
    protected $appends = [
        'test1',
    ];

    /**
     * @param  Builder  $query
     * @param  array  $conditions
     */
    protected static function setConditions(Builder $query, array $conditions)
    {
        if (! empty($conditions['s'])) {
            collect($conditions['s'])->each(function ($search) use ($query) {
                $query->where(function ($builder) use ($search) {
                    /** @var Builder $builder */
                    $builder->where('user_details.name', 'like', "%{$search}%")
                            ->orWhere('user_details.address', 'like', "%{$search}%");
                });
            });
        }
    }

    /**
     * @return array
     */
    protected static function getSearchJoins(): array
    {
        return [
            'user_details' => [
                'first'  => 'user_details.user_id',
                'second' => 'users.id',
            ],
        ];
    }

    /**
     * @return array
     */
    protected static function getSearchOrderBy(): array
    {
        return [
            'users.id' => 'desc',
        ];
    }

    /**
     * @return array
     */
    public static function getCrudAppends(): array
    {
        return [
            'test2',
        ];
    }

    /**
     * @return array
     */
    public static function getCrudListRelations(): array
    {
        return [
            'detail',
        ];
    }

    /**
     * @return array
     */
    public static function getCrudDetailRelations(): array
    {
        return [
            'detail',
        ];
    }

    /**
     * @return array
     */
    public static function getCrudUpdateRelations(): array
    {
        return [
            'detail' => UserDetail::class,
        ];
    }

    /**
     * @return HasOne
     */
    public function detail(): HasOne
    {
        return $this->hasOne(UserDetail::class);
    }

    /**
     * @return string
     */
    public function getTest1Attribute(): string
    {
        return 'test1';
    }

    /**
     * @return string
     */
    public function getTest2Attribute(): string
    {
        return 'test2';
    }
}
