<?php

//定义项目启动时间
define('LARAVEL_START', microtime(true));

//定义文件路径
define('APP_PATH', realpath('../application').'/');
define('SYS_PATH', realpath('../system').'/');
define('BASE_PATH', realpath('../').'/');

//定义文件后缀
define('EXT', '.php');

//引入系统配置文件
require SYS_PATH . 'config' . EXT;


//类自动加载
spl_autoload_register(require SYS_PATH . 'loader' . EXT);


set_exception_handler(function($e)
{
    System\Error::handle($e);
});

set_error_handler(function($number, $error, $file, $line)
{
    System\Error::handle(new ErrorException($error, 0, $number, $file, $line));
});

register_shutdown_function(function()
{
    if ( ! is_null($error = error_get_last()))
    {
        System\Error::handle(new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']));
    }
});

if (System\Config::get('session.driver') != '')
{
    System\Session::load();
}

//获取路由方法
$route = System\Router::route(Request::method(), Request::uri());

//执行路由
$response = $route->call();

if (System\Config::get('session.driver') != '')
{
    System\Session::close();
}

//返回数据
$response->send();
