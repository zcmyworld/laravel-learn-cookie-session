<?php

namespace System;

class Route
{
    public $route;
    
    public $parameters;

    public function __construct($route, $parameters = array())
    {
        $this->route = $route;
        $this->parameters = $parameters;
    }
    
    public function call()
    {
        if (is_callable($this->route))
        {
            $response = call_user_func_array($this->route, $this->parameters);
        }
        
        $response = new Response($response);
        
        return $response;
    }
}
