<?php
declare(strict_types=1);

namespace Technote\CrudHelper\Repositories;

use Technote\CrudHelper\Models\Contracts\Crudable;
use Technote\CrudHelper\Providers\Contracts\ModelInjectionable;
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
     * @param  string  $class
     *
     * @return bool
     */
    private function isSearchable($class)
    {
        return interface_exists('\Technote\SearchHelper\Models\Contracts\Searchable') && is_subclass_of($class, '\Technote\SearchHelper\Models\Contracts\Searchable');
    }

    /**
     * @param  array  $conditions
     *
     * @return LengthAwarePaginator|Builder[]|Collection|Model[]
     */
    public function all(array $conditions)
    {
        if ($this->isSearchable($this->target)) {
            /** @var \Technote\SearchHelper\Models\Contracts\Searchable $class */
            $class    = $this->target;
            $instance = $class::search($conditions);
        } else {
            $instance = $this->instance;
        }

        if ($this->isSearchable($this->target) && isset($conditions[$this->target::getCountName()])) {
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
        return $this->instance->with($this->target::getCrudDetailRelations())->findOrFail((int) $primaryId)->append($this->target::getCrudAppends());
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

        return $this->target::find($record->id);
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

        return $this->target::find($primaryId);
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
