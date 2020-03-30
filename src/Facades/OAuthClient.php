<?php

namespace LemonCMS\LaravelCrud\Facades;

use Illuminate\Support\Facades\Facade;

class OAuthClient extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'OAuthClient';
    }
}
