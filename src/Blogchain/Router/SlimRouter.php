<?php

namespace Blogchain\Router;


use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Blogchain\Core\Application\Blogchain;
use Blogchain\Core\Cache\CacheInterface;
use Slim\App;
use Slim\Container;

class SlimRouter implements Router
{

    /** @var App Slim app */
    private $app;

    private $errorHandler;

    public function __construct()
    {
        $c = new Container();

        $c['settings']['displayErrorDetails'] = Blogchain::instance()->getConfig()->debug;

        $c['errorHandler'] = function ($c) {
            return function ($request, $response, $exception) use ($c) {
                if (!empty($this->errorHandler)) {
                    return ($this->errorHandler)($request, $c['response'], $exception);
                }

                return $c['response']->withStatus(500)
                    ->withHeader('Content-Type', 'text/html')
                    ->write('There has been an error.');
            };
        };

        $c['phpErrorHandler'] = function ($c) {
            return function ($request, $response, $exception) use ($c) {
                if (!empty($this->errorHandler)) {
                    return ($this->errorHandler)(null, $c['response'], $exception);
                }

                return $c['response']->withStatus(500)
                    ->withHeader('Content-Type', 'text/html')
                    ->write('There has been an error.');
            };
        };

        $this->app = new App($c);

        if (!empty(Blogchain::instance()->has(CacheInterface::class))) {
            $this->app->add(function (RequestInterface $request, ResponseInterface $response, $next) {

                /** @var CacheInterface $cache */
                $cache = Blogchain::instance()->get(CacheInterface::class);

                $cacheKey = md5($request->getUri());
                if ($cachedResponse = $cache->cacheForPath($cacheKey)) {
                    $response->getBody()->write($cachedResponse);
                    return $response;
                }

                /** @var ResponseInterface $response */
                $response = $next($request, $response);

                $cache->setCacheForPath($cacheKey, $response->getBody()->__toString());

                return $response;
            });
        }

    }

    public function get($path, $callback)
    {
        $this->app->get($path, $callback);
    }

    public function post($path, $callback)
    {
        $this->app->post($path, $callback);
    }

    public function delete($path, $callback)
    {
        $this->app->delete($path, $callback);
    }

    public function put($path, $callback)
    {
        $this->app->put($path, $callback);
    }

    public function options($path, $callback)
    {
        $this->app->options($path, $callback);
    }

    public function middleware($callback)
    {
        $this->app->add($callback);
    }

    public function setErrorHandler($callback)
    {
        $this->errorHandler = $callback;
    }


    public function start()
    {
        $this->app->run();
    }


}