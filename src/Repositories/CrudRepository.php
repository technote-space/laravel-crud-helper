<?php
declare(strict_types=1);

namespace Technote\CrudHelper\Repositories;

use Technote\CrudHelper\Models\Contracts\Crudable;
use Technote\CrudHelper\Providers\Contracts\ModelInjectionable;
use Technote\SearchHelper\Models\Contracts\Searchable as SearchableContract;
use Technote\SearchHelper\Models\Traits\Searchable;
use Eloquent;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CrudRepository
 * @package Technote\CrudHelper\Repositories
 */
class CrudRepository implements ModelInjectionable
{
    /** @var string|Eloquent|Crudable $target */
    private $target;

    /** @var Model|Crudable $instance */
    private $instance;

    /**
     * @param  string  $target
     * @SuppressWarnings(PHPMD.MissingImport)
     */
    public function setTarget(string $target)
    {
        $this->target   = $target;
        $this->instance = new $this->target;
    }

    /**
     * @param  array  $conditions
     *
     * @return Searchable[]|LengthAwarePaginator|Builder[]|Collection|Model[]
     */
    public function all(array $conditions)
    {
        if (is_subclass_of($this->target, SearchableContract::class)) {
            /** @var SearchableContract $class */
            $class    = $this->target;
            $instance = $class::search($conditions);
        } else {
            $instance = $this->instance;
        }

        if (is_subclass_of($this->target, SearchableContract::class) && isset($conditions[$this->target::getCountName()])) {
            return $instance->with($this->target::getCrudListRelations())->get();
        }

        return $instance->with($this->target::getCrudListRelations())->paginate($conditions[$this->target::getPerPageName()] ?? null);
    }

    /**
     * @param  mixed  $primaryId
     *
     * @return Eloquent|Eloquent[]|Collection|Model
     */
    public function get($primaryId)
    {
        return $this->instance->with($this->target::getCrudDetailRelations())->findOrFail((int) $primaryId);
    }

    /**
     * @param  \Illuminate\Support\Collection  $data
     *
     * @return Eloquent|Model
     */
    public function create(\Illuminate\Support\Collection $data)
    {
        $record = $this->target::create($data->shift());
        $data->each(function ($data) use ($record) {
            $relation = $data['relation'];
            $record->$relation()->create(array_merge($data['attributes'], [$record->getForeignKey() => $record->getAttribute('id')]));
        });

        return $record;
    }

    /**
     * @param $primaryId
     * @param  \Illuminate\Support\Collection  $data
     *
     * @return Eloquent|Model
     */
    public function update($primaryId, \Illuminate\Support\Collection $data)
    {
        $record = $this->target::findOrFail($primaryId);
        $record->fill($data->shift())->save();
        $data->each(function ($data) use ($record) {
            $relation = $data['relation'];
            $record->$relation()->save($data['target']::updateOrCreate([$record->getForeignKey() => $record->getAttribute('id')], $data['attributes']));
        });

        return $record;
    }

    /**
     * @param  mixed  $primaryId
     *
     * @return array
     */
    public function delete($primaryId)
    {
        return ['result' => $this->target::destroy((int) $primaryId)];
    }
}
