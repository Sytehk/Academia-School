<?php

use App\Providers\AppServiceProvider;
use App\Providers\RepositoryServiceProvider;
use App\Providers\SystemServiceProvider;
use App\Providers\ViewServiceProvider;

return [
    AppServiceProvider::class,
    SystemServiceProvider::class,
    RepositoryServiceProvider::class,
    ViewServiceProvider::class,
];
