<?php

class View
{
    public static function share($name, $menu)
    {
    }
}

function action($name, $parameters)
{
    return $name;
}

function env($boolean, $default)
{
    return $default;
}

function route($name, $route_parameters)
{
    return $name;
}

function secure_url($url)
{
    return 'https://localhost/'.$url;
}

function url($url)
{
    return 'https://localhost/'.$url;
}

class Request
{
    public static function url()
    {
        return true;
    }
}
