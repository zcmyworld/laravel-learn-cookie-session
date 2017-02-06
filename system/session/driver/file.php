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
