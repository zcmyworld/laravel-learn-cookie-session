<?php

namespace System;

class Session
{
    private static $driver;
    
    private static $session = array();


    public static function driver()
    {
        if (is_null(static::$driver))
        {
            static::$driver = Session\Factory::make(Config::get('session.driver'));
        }

        return static::$driver;
    }

    public static function load()
    {
        // 根据 cookie 来加载 session
        if ( ! is_null($id = Cookie::get('laravel_session')))
        {
            static::$session = static::driver()->load($id);
        }

        // 如果 session 不存在或者 session 超时
        if (is_null($id) or is_null(static::$session) or (time() - static::$session['last_activity']) > (Config::get('session.lifetime') * 60))
        {
            static::$session['id'] = Str::random(40);
            static::$session['data'] = array();
        }

        // 更新 session 时间
        static::$session['last_activity'] = time();
    }


    public static function has($key)
    {
        return array_key_exists($key, static::$session['data']);
    }

    public static function get($key, $default = null)
    {
        if (static::has($key))
        {
            if (array_key_exists($key, static::$session['data']))
            {
                return static::$session['data'][$key];
            }
        }

        return $default;
    }

    public static function put($key, $value)
    {
        static::$session['data'][$key] = $value;
    }

    public static function forget($key)
    {
        unset(static::$session['data'][$key]);
    }


    public static function close()
    {
        static::driver()->save(static::$session);

        if ( ! headers_sent())
        {
            $lifetime = (Config::get('session.expire_on_close')) ? 0 : Config::get('session.lifetime');

            Cookie::put('laravel_session', static::$session['id'], $lifetime, Config::get('session.path'), Config::get('session.domain'), Config::get('session.https'));
        }

        // 随机清除过期 session
        if (mt_rand(1, 100) <= 2)
        {
            static::driver()->sweep(time() - (Config::get('session.lifetime') * 60));
        }
    }
}
