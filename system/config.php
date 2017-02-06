<?php

namespace System;

class Config
{
    private static $items = array();

    public static function get($key)
    {
        //调用参数解析函数
        list($file, $key) = static::parse($key);

        //加载配置文件
        static::load($file);

        //返回对应的配置
        return (array_key_exists($key, static::$items[$file])) ? static::$items[$file][$key] : null;
    }
    
    private static function parse($key)
    {
        $segments = explode('.', $key);

        // 当参数格式不对的时候，抛出异常
        if (count($segments) < 2)
        {
            throw new \Exception("Invalid configuration key [$key].");
        }

        return array($segments[0], implode('.', array_slice($segments, 1)));
    }

    public static function load($file)
    {
        // 当配置文件已经被加载过，不再重复加载
        if (array_key_exists($file, static::$items))
        {
            return;
        }

        //配置文件不存在的时候，抛出异常
        if ( ! file_exists($path = APP_PATH.'config/'.$file.EXT))
        {
            throw new \Exception("Configuration file [$file] does not exist.");
        }

        //加载配置文件并赋值到static::$items中
        static::$items[$file] = require $path;
    }
}
