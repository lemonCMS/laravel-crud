<?php

namespace TestApp\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use LemonCMS\LaravelCrud\Model\CrudTrait;

class Blog extends Model
{
    use CrudTrait;
    use SoftDeletes;

    /**
     * Provided includes can be requested through the comma separated include param
     * E.g.
     * /api/blogs?include=tags.
     *
     * @var array
     */
    protected $includes = [
        'tags',
    ];

    /**
     * Fields that can be used to sort by through the order_field and order_dir param
     * E.g.
     * /api/blogs?include=tags&order_field=created_at&order_dir=desc.
     *
     * @var array
     */
    protected $orderFields = [
        'id', 'title', 'created_at', 'modified_at',
    ];

    /**
     * Define fields where a full matched can be performed on
     * E.g.
     * /api/blogs?id=1.
     *
     * Or provide a callback to create a custom filter
     * E.g.
     * /api/blogs?title=blo
     *
     * @return array
     */
    protected function search()
    {
        return [
            'id',
            'title' => function (Builder $query, $value) {
                return $query->where('title', 'like', "%{$value}%");
            },
        ];
    }

    /**
     * Just a default relation.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tags()
    {
        return $this->hasMany('TestApp\Models\BlogTag');
    }
}
