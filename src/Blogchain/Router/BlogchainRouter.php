<?php

namespace Blogchain\Router;


use Blogchain\Core\Application\Blogchain;
use Blogchain\Core\Plugin\Plugin;

class BlogchainRouter implements Plugin
{
    public function register()
    {
        Blogchain::instance()->share(Router::class, SlimRouter::class);
    }

}