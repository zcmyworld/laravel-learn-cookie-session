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