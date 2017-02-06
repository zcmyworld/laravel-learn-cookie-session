<?php

namespace System;

class Request
{

    public static $uri;
    
    public static function uri()
    {
        if ( ! is_null(static::$uri))
        {
            return static::$uri;
        }

        if ( ! isset($_SERVER['REQUEST_URI']))
        {
            throw new \Exception('Unable to determine the request URI.');
        }

        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        return static::$uri = static::tidy($uri);
    }

    // 格式化url
    private static function tidy($uri)
    {
        return ($uri != '/') ? strtolower(trim($uri, '/')) : '/';
    }

    public static function method()
    {
        return (isset($_POST['request_method'])) ? $_POST['request_method'] : $_SERVER['REQUEST_METHOD'];
    }
}
