<?php

return array(
    'GET /' => function()
    {
        return View::make('home/index')->bind("key", "Let's learn laravel!");
    }
);
