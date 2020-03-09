<?php

namespace TestApp\Http\Controllers;

use Illuminate\Routing\Controller;
use LemonCMS\LaravelCrud\Http\Controllers\CrudControllerTrait;
use TestApp\Models\Blog;

class NoEventController extends Controller
{
    use CrudControllerTrait;

    protected function initCrud()
    {
        $this->setModel(Blog::class);
    }
}
