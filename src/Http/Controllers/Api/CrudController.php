<?php
declare(strict_types=1);

namespace Technote\CrudHelper\Http\Controllers\Api;

use Eloquent;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Technote\CrudHelper\Http\Requests\SearchRequest;
use Technote\CrudHelper\Http\Requests\UpdateRequest;
use Technote\CrudHelper\Repositories\CrudRepository;

class CrudController
{
    /**
     * @param SearchRequest $request
     * @param CrudRepository $repository
     *
     * @return LengthAwarePaginator|Builder[]|Collection|Model[]
     */
    public function index(SearchRequest $request, CrudRepository $repository)
    {
        return $repository->all($request->getConditions());
    }

    /**
     * @param $primaryId
     * @param CrudRepository $repository
     *
     * @return Eloquent|Eloquent[]|Collection|Model
     */
    public function show($primaryId, CrudRepository $repository)
    {
        return $repository->get($primaryId);
    }

    /**
     * @param UpdateRequest $request
     * @param CrudRepository $repository
     *
     * @return Eloquent|Model
     */
    public function store(UpdateRequest $request, CrudRepository $repository)
    {
        return $repository->create($request->getData());
    }

    /**
     * @param UpdateRequest $request
     * @param int $primaryId
     * @param CrudRepository $repository
     *
     * @return Eloquent|Model
     */
    public function update(UpdateRequest $request, int $primaryId, CrudRepository $repository)
    {
        return $repository->update($primaryId, $request->getData());
    }

    /**
     * @param int $primaryId
     * @param CrudRepository $repository
     *
     * @return array
     */
    public function destroy(int $primaryId, CrudRepository $repository): array
    {
        return $repository->delete($primaryId);
    }
}
