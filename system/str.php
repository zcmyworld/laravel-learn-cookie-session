<?php

namespace System;

class Str
{
    public static function random($length = 16)
    {
        $pool = str_split('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', 1);

        $value = '';

        for ($i = 0; $i < $length; $i++)
        {
            $value .= $pool[mt_rand(0, 61)];
        }

        return $value;
    }
}
