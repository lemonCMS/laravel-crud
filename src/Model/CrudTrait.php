<?php

namespace LemonCMS\LaravelCrud\Model;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Http\Request;
use LemonCMS\LaravelCrud\Exceptions\UnauthorizedException;

/**
 * Trait CrudTrait.
 *
 * @property string $userRelation
 * @property string $userIdColumn
 */
trait CrudTrait
{
    public $userRelation;

    public $userIdColumn;

    public function __construct()
    {
        if (! isset($this->userRelation)) {
            if (method_exists($this, 'users')) {
                $this->userRelation = 'users';
            } elseif (method_exists($this, 'user')) {
                $this->userRelation = 'user';
            }
        }

        if (! isset($this->userIdColumn)) {
            $this->userIdColumn = 'id';
        }
    }

    /**
     * @param bool $privateAccess
     */
    public function scopePaginatedResources(Builder $query, Request $request, $privateAccess = false): void
    {
        if ($privateAccess) {
            $this->checkRelation();

            $query->whereHas($this->userRelation, function (Builder $hasQuery) use ($request) {
                $hasQuery->where($this->userIdColumn, $request->user()[$this->userIdColumn]);
            });
        }

        $this->runIncludes($query, $request->get('include', null));
        $this->runSearch($query, $request->all());
        $this->runSort(
            $query,
            $request->get('order_field', null),
            $request->get('order_dir', null)
        );

        \Response::json($query->paginate($this->perPage))
            ->setStatusCode(200)->send();
    }

    /**
     * Shallow check if the relation to the User model
     * does exist on the current model.
     *
     * TODO: follow the dot notation path and check if
     *  all exist in all models.
     */
    private function checkRelation()
    {
        [$relation] = explode('.', $this->userRelation);

        if (! method_exists($this, $relation)) {
            throw RelationNotFoundException::make($this->getModel(), $relation);
        }
    }

    /**
     * Load relations.
     *
     * protected $includes = [
     *  'users'
     * ];
     */
    private function runIncludes(Builder $query, string $includes = null): Builder
    {
        $list = $this->parseInclude($includes);
        if ($list) {
            $query->with($list);
        }

        return $query;
    }

    /**
     * @param $includes
     *
     * @return array|null
     */
    private function parseInclude($includes)
    {
        if (! $includes || ! isset($this->includes)) {
            return;
        }

        $list = explode(',', $includes);
        foreach ($list as $key => $item) {
            if (! in_array($item, $this->includes)) {
                throw new \InvalidArgumentException('Include `'.$item.'` is not configured on the model');
            }
        }
        if (count($list)) {
            return $list;
        }
    }

    private function runSearch(Builder $query, array $params): Builder
    {
        if (! method_exists($this, 'search')) {
            return $query;
        }

        foreach ($this->search() as $key => $column) {
            if (is_numeric($key) && isset($params[$column])) {
                $this->filterFullMatch($query, $column, $params[$column]);
                continue;
            }

            if (! isset($params[$key])) {
                continue;
            }

            if (is_callable($column)) {
                $column($query, $params[$key], $params);
            }
        }

        return $query;
    }

    /**
     * @return Builder
     */
    private function filterFullMatch(Builder $query, string $column, string $value)
    {
        return $query->where($column, $value);
    }

    private function runSort(Builder $query, string $column = null, string $orderDir = null): Builder
    {
        if (! isset($this->orderFields) || ! $column) {
            return $query;
        }

        if (! in_array($orderDir, ['asc', 'desc'])) {
            $orderDir = 'asc';
        }

        if (in_array($column, $this->orderFields)) {
            $query->orderBy($column, $orderDir);
        }

        return $query;
    }

    public function scopeIsOwner(Builder $query, int $modelId, Request $request): bool
    {
        return $this->scopeResource($query, $modelId, $request, true)->get()->isNotEmpty();
    }

    public function scopeResource(Builder $query, int $modelId, Request $request, bool $checkOwner = false): Builder
    {
        if ($checkOwner && ! $request->user()) {
            throw new UnauthorizedException('Access denied for the resource.');
        }

        if ($checkOwner) {
            $this->checkRelation();
            $query->whereHas($this->userRelation, function (Builder $hasQuery) use ($request) {
                $hasQuery->where($this->userIdColumn, $request->user()[$this->userIdColumn]);
            });
        }
        $query->where($this->primaryKey, $modelId);
        $this->runIncludes($query, $request->get('include', null));

        return $query;
    }

    public function scopeViewResource(Builder $query, int $modelId, Request $request, bool $checkOwner = false)
    {
        if ($checkOwner && $request->user()) {
            $this->checkRelation();
            $query->whereHas($this->userRelation, function (Builder $hasQuery) use ($request) {
                $hasQuery->where($this->userIdColumn, $request->user()[$this->userIdColumn]);
            });
        }

        \Response::json($this->scopeResource($query, $modelId, $request, $checkOwner)->firstOrFail())
            ->setStatusCode(200)
            ->send();
    }

    /**
     * @return Builder|Model
     */
    public function scopeFindWithOwner(Builder $query, int $modelId, Request $request)
    {
        $this->checkRelation();
        $query->where($this->primaryKey, $modelId);
        $query->whereHas($this->userRelation, function (Builder $hasQuery) use ($request) {
            $hasQuery->where($this->userIdColumn, $request->user()[$this->userIdColumn]);
        });

        return $query->firstOrFail();
    }

    /**
     * Load "missing" relations.
     *
     * Define in your model what relations may be loaded
     * from the param includes.
     *
     * protected $includes = [
     *  'users'
     * ];
     *
     * @param Model $model
     * @param string|null $includes
     * @return Model
     */
    private function runLoadMissing(Model $model, string $includes = null): Model
    {
        $list = $this->parseInclude($includes);
        if ($list) {
            $model->loadMissing($list);
        }

        return $model;
    }
}
