<?php namespace System;

class Error {

    /**
     * Error levels.
     *
     * @var array
     */
    public static $levels = array(
        0                  => 'Error',
        E_ERROR            => 'Error',
        E_WARNING          => 'Warning',
        E_PARSE            => 'Parsing Error',
        E_NOTICE           => 'Notice',
        E_CORE_ERROR       => 'Core Error',
        E_CORE_WARNING     => 'Core Warning',
        E_COMPILE_ERROR    => 'Compile Error',
        E_COMPILE_WARNING  => 'Compile Warning',
        E_USER_ERROR       => 'User Error',
        E_USER_WARNING     => 'User Warning',
        E_USER_NOTICE      => 'User Notice',
        E_STRICT           => 'Runtime Notice'
    );

    /**
     * Handle an exception.
     *
     * @param  Exception  $e
     * @return void
     */
    public static function handle($e)
    {
        // 清空输出缓冲区
        if (ob_get_level() > 0)
        {
            ob_clean();
        }

        // 获取错误级别
        $severity = (array_key_exists($e->getCode(), static::$levels)) ? static::$levels[$e->getCode()] : $e->getCode();

        // 获取错误文件
        $file = $e->getFile();

        // 格式化错误信息
        $message = rtrim($e->getMessage(), '.');

        // 根据配置文件决定是否记录日志
        if (Config::get('error.log'))
        {
            Log::error($message.' in '.$e->getFile().' on line '.$e->getLine());
        }


        // 根据配置文件来决定是否显示异常信息
        if (Config::get('error.detail'))
        {
            $view = View::make('error/exception')
                ->bind('severity', $severity)
                ->bind('message', $message)
                ->bind('file', $file)
                ->bind('line', $e->getLine())
                ->bind('trace', $e->getTraceAsString())
                ->bind('contexts', static::context($file, $e->getLine()));

            $response = new Response($view, 500);
            $response->send();
        }
        else
        {
            $response = new Response(View::make('error/500'), 500);
            $response->send();
        }

        exit(1);
    }

    /**
     * 根据错误行数获取上下文
     *
     * @param  string  $path
     * @param  int     $line
     * @param  int     $padding
     * @return array
     */
    private static function context($path, $line, $padding = 5)
    {
        // 判断文件是否存在
        if (file_exists($path))
        {
            // 读取文件， 以数组的形式存储整个文件
            $file = file($path, FILE_IGNORE_NEW_LINES);

            // 保证数组不为空
            array_unshift($file, '');

            // 计算初始偏移
            $start = $line - $padding;

            if ($start < 0)
            {
                $start = 0;
            }

            // 计算上下文长度
            $length = ($line - $start) + $padding + 1;

            if (($start + $length) > count($file) - 1)
            {
                $length = null;
            }

            return array_slice($file, $start, $length, true);
        }

        return array();
    }

}