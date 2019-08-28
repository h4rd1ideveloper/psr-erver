<?php


namespace App\http;


/**
 * Class HttpHelper
 * @package App\http
 */
class HttpHelper
{
    /**
     * @param string|null $headerContent
     */
    public static function setHeader(string $headerContent = 'Content-Type: application/json')
    {
        header(sprintf("%s", $headerContent));
    }
    /**
     * @return array
     */
    public static function getallheaders()
    {
        $parseKey = function ($name) {
            return str_replace(
                ' ', '-', ucwords(
                    strtolower(
                        str_replace('_', ' ', substr($name, 5))
                    )
                )
            );
        };
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[$parseKey($name)] = $value;
            }
        }
        return $headers;

    }
}