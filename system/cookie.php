<?php

namespace System;

class Cookie
{
    
    public static function get($key, $default = null)
    {
        return (array_key_exists($key, $_COOKIE)) ? $_COOKIE[$key] : $default;
    }

    
    public static function put($key, $value, $minutes = 0, $path = '/', $domain = null, $secure = false)
    {
        if ($minutes < 0)         
        {
            unset($_COOKIE[$key]);
        }
        
        return setcookie($key, $value, ($minutes != 0) ? time() + ($minutes * 60) : 0, $path, $domain, $secure);
    }
    
    public static function forget($key)
    {
        return static::put($key, null, -60);
    }
}