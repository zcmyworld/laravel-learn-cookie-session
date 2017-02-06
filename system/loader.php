<?php

return function($class)
{
    $file = strtolower(str_replace('\\', '/', $class));

    // 先判断当前调用的类在配置文件中是否定义了别名
    if (array_key_exists($class, $aliases = System\Config::get('application.aliases')))
    {
        return class_alias($aliases[$class], $class);
    }
    elseif (file_exists($path = BASE_PATH.$file.EXT))
    {
        require $path;
    }
    elseif (file_exists($path = APP_PATH.$file.EXT))
    {
        require $path;
    }
};