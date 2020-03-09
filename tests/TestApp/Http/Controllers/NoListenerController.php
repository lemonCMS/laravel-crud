<?php

namespace TestApp\Http\Controllers;

use LemonCMS\LaravelCrud\Http\Controllers\CrudControllerTrait;
use TestApp\Models\Blog;

class NoListenerController extends AbstractController
{
    use CrudControllerTrait;

    protected function initCrud()
    {
        $this->setModel(Blog::class);
    }
}
