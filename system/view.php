<?php

namespace System;

class View
{
    public $view;

    public $data = array();
    public $content = "";


    public function __construct($view, $data = array())
    {
        $this->view = $view;
        $this->data = $data;

        $this->content = $this->load($view);
    }


    public static function make($view, $data = array())
    {
        return new self($view, $data);
    }

    private function load($view)
    {
        if (file_exists($path = APP_PATH.'views/'.$view.EXT))
        {
            return file_get_contents($path);
        }

        elseif (file_exists($path = SYS_PATH.'views/'.$view.EXT))
        {
            return file_get_contents($path);
        }

        else
        {
            throw new \Exception("View [$view] doesn't exist.");
        }
    }

    public function bind($key, $value)
    {
        $this->data[$key] = $value;
        return $this;
    }

    public function get()
    {
        extract($this->data);

        ob_start();

        echo eval('?>' . $this->content);

        return ob_get_clean();
    }

    public function __toString()
    {
        return $this->get();
    }

}
