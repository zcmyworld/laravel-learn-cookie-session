<?php

namespace System;

class Router
{
    public static $routes;
    
    public static function route($method, $uri)
    {
        $uri = ($uri != '/') ? '/' . $uri : $uri;

        static::$routes = require APP_PATH.'routes'.EXT;
        
        if (isset(static::$routes[$method.' '.$uri]))
        {
            return new Route(static::$routes[$method.' '.$uri]);
        }
    }
    
}
