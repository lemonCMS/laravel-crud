<?php

namespace TestApp\Models;

use Illuminate\Database\Eloquent\Model;

class BlogTag extends Model
{
    protected $casts = [
        'blog_id' => 'integer',
    ];
}
