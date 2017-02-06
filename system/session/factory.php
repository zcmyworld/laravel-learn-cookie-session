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