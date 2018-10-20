<?php

namespace Blogchain\Router;

interface Router
{
    public function setErrorHandler($callback);

    public function get($path, $callback);

    public function post($path, $callback);

    public function delete($path, $callback);

    public function put($path, $callback);

    public function options($path, $callback);

    public function middleware($callback);


    public function start();
}