<?php

namespace App\controller;

use App\routes\Router;

/**
 * Class controller
 */
abstract class Controller
{
    /**
     * @param string $_name
     * @param array $vars
     */
    protected static final function view($_name, array $vars = [])
    {
        !file_exists(sprintf("%s/../view/%s.php", __DIR__, $_name)) && die(print_r(["view {$_name} not found!", __DIR__]));
        include_once(sprintf("%s/../view/%s.php", __DIR__, $_name));
    }

    /**
     * @return array | null
     * @see Router::getRequest()
     */
    protected static final function request()
    {
        return (Router::getRequest()) ? Router::getRequest() : null;
    }

    /**
     * @return mixed
     */
    protected static final function getRequestBody()
    {
        return Router::getRequestBody();
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected final function getUrlParams(string $key = "")
    {
        return isset(Router::getUrlParams()[$key]) ? Router::getUrlParams()[$key] : Router::getUrlParams();
    }
    protected final function requestObject(){
        return Router::requestObject();
    }
    protected final function serverObject(){
        return Router::serverObject();
    }

    /**
     * @param string $to
     */
    protected final function redirect($to)
    {
        $url = sprintf("%s://%s", isset($_SERVER['HTTPS']) ? 'https' : 'http', $_SERVER['HTTP_HOST']);
        $folders = explode('?', $_SERVER['REQUEST_URI'])[0];
        header(sprintf("Location:%s%s?r=%s", $url, $folders, $to));
        exit();
    }
}