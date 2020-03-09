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

    protected $includes = [
        'tags',
    ];

    protected $orderFields = [
        'id', 'title', 'created_at', 'modified_at',
    ];

    public function tags()
    {
        return $this->hasMany('TestApp\Models\BlogTag');
    }

    protected function search()
    {
        return [
            'id',
            'title' => function (Builder $query, $value) {
                return $query->where('title', 'like', "%{$value}%");
            },
        ];
    }
}
