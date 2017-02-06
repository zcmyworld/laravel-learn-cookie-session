## 需求设计

1. 开发者可以在配置文件中控制是否使用 session
2. 开启 session 之后，开发者可以在业务代码中轻松读写 session

## 实现

session 中包含4个核心方法

1. load() http请求开始时为用户加载session
2. close() http请求结束时记录用户session状态
3. get() 读取 session 中的值
4. put() 向session写值

在 system 里新增文件 session.php:

	<?php

	namespace System;

	class Session
	{
	    private static $session = array();

	    public static function load()
	    {
	    }

	    public static function get($key, $default = null)
	    {

	    }

	    public static function put($key, $value)
	    {

	    }

	    public static function close()
	    {

	    }
	}

在 application/config 目录下新增文件 session.php, 记录用于控制 session 的一些配置信息

	<?php

	return array(

	    // session 使用文件驱动
	    'driver' => 'file',

	    // session 生命周期
	    'lifetime' => 60,

	    // 是否在用户关闭浏览器的时候清空session
	    'expire_on_close' => false,

	    // session cookie 所属 path
	    'path' => '/',

	    // session cookie 所属域名
	    'domain' => null,

	);


session 可以有多种保存形式，可以保存在 MySQL，保存在 Redis, 保存在一切可持久化的地方，这里的示例为保存在文件中，通过配置文件中的饿 driver 选择来决定保存的位置，同时也用于标记是否启用了 session，在 public/index.php 中的

	$route = System\Router::route(Request::method(), Request::uri());

前加上：

	if (System\Config::get('session.driver') != '')
	{
	    System\Session::load();
	}

意在处理请求前先加载 session

在 public/index.php 中的

	$response->send();

前加上

	if (System\Config::get('session.driver') != '')
	{
	    System\Session::close();
	}

意在请求结束前保存 session 状态。

我们希望将 session 的实现与存储分离，便于更换 session 的存储引擎，先构造一个接口，在 application 目录下新建 session 目录，新建 driver.php 文件：

	<?php

	namespace System\Session;

	interface Driver {

		// 加载 session
		public function load($id);

		// 保存 session
		public function save($session);

		// 根据id删除 session
		public function delete($id);

		// 删除超时 session
		public function sweep($expiration);

	}

再在 appilication/session 目录下创建 driver 目录，所有的存储引擎都存放在这里，本实例新建 file.php 并实现 driver interface 用于作为文件存储的驱动：

	<?php

	namespace System\Session\Driver;

	class File implements \System\Session\Driver
	{
	    public function load($id)
	    {

	    }

	    public function save($session)
	    {

	    }


	    public function delete($id)
	    {

	    }


	    public function sweep($expiration)
	    {

	    }
	}

在 application 下创建 sessions 目录，赋予系统权限	 02777 ，用于保存 session 文件，其中，每一个用于的session保存在一个文件中，以一个随机字符串作为文件名同时也作为sessionid，文件里面的内容是序列化之后的字符，将 system/session/driver/file.php 改为：

	<?php

	namespace System\Session\Driver;

	class File implements \System\Session\Driver
	{
	    public function load($id)
	    {
	        if (file_exists($path = APP_PATH.'sessions/'.$id))
	        {
	            // 将数据反序列化返回
	            return unserialize(file_get_contents($path));
	        }
	    }

	    public function save($session)
	    {

	        // 将数据序列化存储，写文件过程使用文件锁
	        file_put_contents(APP_PATH.'sessions/'.$session['id'], serialize($session), LOCK_EX);
	    }

	    public function delete($id)
	    {
	        // 删除对应文件
	        @unlink(APP_PATH.'sessions/'.$id);
	    }

	    // 删除超时 session
	    public function sweep($expiration)
	    {
	        foreach (glob(APP_PATH.'sessions/*') as $file)
	        {
	            // -----------------------------------------------------
	            // If the session file has expired, delete it.
	            // -----------------------------------------------------
	            if (filetype($file) == 'file' and filemtime($file) < $expiration)
	            {
	                @unlink($file);
	            }
	        }
	    }
	}

再通过一个session工厂来决定使用的是哪一个存储驱动，在 system/session/driver 目录下新增文件： factory.php

	<?php namespace System\Session;

	class Factory {

	    public static function make($driver)
	    {
	        switch ($driver)
	        {
	            case 'file':
	                return new Driver\File;

	            default:
	                throw new \Exception("Session driver [$driver] is not supported.");
	        }
	    }

	}

在 system/session.php 类中加上 driver 对应的属性和加载方法：

	private static $driver;

    public static function driver()
    {
        if (is_null(static::$driver))
        {
            static::$driver = Session\Factory::make(Config::get('session.driver'));
        }

        return static::$driver;
    }

修改 system/session.php 中的 load 方法，如果已有 session 则使用已有 session， 如果没有 session 则生成一个随机id的session 并更新活跃时间

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

在 http 请求结束之前， 所有的 session 信息都会保存在 session 类的  $session 变量里面，于是修改 get() put() 方法，并新增 has() forget() 方法得出：

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


session 的除了在服务端做持久化，还需要在客户端 cookie 中保存对应的id，于是封装 php 内置 变量$_COOKIE，和 setcookie方法， 在 system 目录下新增文件 cookie.php :

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

最后完善  system/session 类的 close() 方法

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

其中，每次请求有一定概率会触发过期 session 清理程序

整个框架项目结构形如：

* application
	* |- config
		* |-applicationi.php
		* |-error.php
		* |-session.php
	* |- views
		* |-home
			* index.php
	* |- routes.php
* public
	* |- index.php
* system
	* |- session
		* |- driver
			* |- file.php
		* |- driver.php
		* |- factory.php
	* |- config.php
	* |- cookie.php
	* |- error.php
	* |- loader.php
	* |- request.php
	* |- response.php
	* |- route.php
	* |- session.php
	* |- str.php
	* |- router.php
	* |- view.php