<?php

namespace App\routes;

use App\assets\lib\Helpers;
use App\http\Request;
use App\http\Response;
use App\http\Stream;
use Closure;

/**
 * Class Router
 * @method get(string $string, Closure $param)
 * @method post(string $string, Closure $param)
 * @method patch(string $string, Closure $param)
 * @method put(string $string, Closure $param)
 * @method delete(string $string, Closure $param)
 */
class Router
{
    /**
     * @var array
     */
    private static $params = array();
    /**
     * @var array
     */
    private static $files = array();
    /**
     * @var array
     */
    private $routes = array();

    /**
     * @return mixed
     */
    public static function getUrlParams()
    {
        return $_GET;
    }

    /**
     * @return mixed
     */
    public static function requestObject()
    {
        return $_REQUEST;
    }

    public static function serverObject()
    {
        return $_SERVER;
    }

    /**
     *  Getter [$_FILES]
     * @return array
     */
    public static function getRequestFile()
    {
        return self::$files;
    }

    /**
     *  Getter  [$_GET | $_POST]
     * @return array
     */
    public static function getRequest()
    {
        return self::$params;
    }

    /**
     * Debugger Closures registered
     */
    public function debugger()
    {
        var_dump($this->routes);
    }

    /**
     * @param string $method
     * @param array $args
     * @return bool
     */
    public function __call($method, array $args)
    {
        $method = strtolower($method);
        if (!$this->validate($method))
            return false;
        [$route, $action] = $args;
        /**
         *To old Versions of the php
         */
        //$route = $args[0];
        //$action = $args[1];
        if (!isset($action) or !is_callable($action))
            return false;

        $this->routes[$method][$route] = $action;
        return true;
    }

    /**
     * Allow method accept 'get', 'post', 'patch', 'put', 'delete'
     * @param string $method
     * @return bool
     */
    private function validate($method)
    {
        return in_array($method, ['get', 'post', 'patch', 'put', 'delete']);
    }

    /**
     * Dá início a aplicação, verificando se existem rotas
     * com o método HTTP atual (post ou get), se existe a rota definida pelo
     * parâmetro GET r. E por fim chamando o callable da rota correspondente,
     * finalizando a aplicação exibindo o seu retorno (a resposta do controller).
     */
    public function run()
    {
        $method = strtolower($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : 'get';
        $route = isset($_SERVER['REQUEST_URI']) ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : '/';//$_SERVER['REQUEST_URI']
        !isset($this->routes[$method]) && die(//Debugger
        print_r(['405 Method not allowed', $method, parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)])
        );
        !isset($this->routes[$method][$route]) && die(//Debugger
        print_r(['404 Error', $method, sprintf("'%s'", parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))])
        );
        self::$params = $this->getParams($method);
        self::$files = $this->getFiles();
        /**
         * Create a body stream by Method
         */
        $body = new Stream(fopen('php://temp', 'r+'));
        $body->write(
            Helpers::toJson(
                array_merge(
                    array_merge($_GET, $_POST),
                    $this->getRequestBody()??array()
                )
            )
        );
        /**
         * Put into body request every content provided
         */
        /**
         * Request Factory
         */
        $request = new Request($method, $route, array(), $body);
        //echo $request->getBody() ;
        /**
         * Response Factory
         */
        $response = new Response();
        die($this->routes[$method][$route]($request, $response));
    }

    /**
     *  Only to GET POST
     * @param string $method
     * @return mixed
     */
    private function getParams($method)
    {
        return strtolower($method) == 'get' ? $_GET : $_POST;
    }

    /**
     * Acessa a variavel global $_FILES
     * enviados pelo cliente.
     */
    private function getFiles()
    {
        return $_FILES;
    }

    /**
     * @return array
     */
    public static function getRequestBody()
    {
        return Helpers::jsonToArray(file_get_contents("php://input"));
    }
}