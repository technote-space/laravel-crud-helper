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
    private $repository;

    public function __construct(CrudRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param SearchRequest $request
     *
     * @return LengthAwarePaginator|Builder[]|Collection|Model[]
     */
    public function index(SearchRequest $request)
    {
        return $this->repository->all($request->getConditions());
    }

    /**
     * @param $primaryId
     *
     * @return Eloquent|Eloquent[]|Collection|Model
     */
    public function show($primaryId)
    {
        return $this->repository->get($primaryId);
    }

    /**
     * @param UpdateRequest $request
     *
     * @return Eloquent|Model
     */
    public function store(UpdateRequest $request)
    {
        return $this->repository->create($request->getData());
    }

    /**
     * @param UpdateRequest $request
     * @param int $primaryId
     *
     * @return Eloquent|Model
     */
    public function update(UpdateRequest $request, int $primaryId)
    {
        return $this->repository->update($primaryId, $request->getData());
    }

    /**
     * @param int $primaryId
     *
     * @return array
     */
    public function destroy(int $primaryId): array
    {
        return $this->repository->delete($primaryId);
    }
}
