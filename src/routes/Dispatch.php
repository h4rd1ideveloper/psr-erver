<?php
/**
 * Setup imports
 */

namespace App\routes;

use App\assets\lib\Helpers;
use App\controller\AppController;
use http\Exception\InvalidArgumentException;

/**
 * Setup Object routes closures
 */
class Dispatch
{
    /**
     * @var array|null
     */
    private $routes;

    /**
     * Closures init.
     * @param array|null $closures
     */
    public function __construct(array $closures = null)
    {
        $this->routes = $closures ?? array(
                'getFields' => function () {
                    return AppController::enelFields();
                },
                'count' => function () {
                    return AppController::checkCount();
                },
                'check' => function () {
                    return AppController::check();
                },
                'test' => function () {
                    return AppController::test();
                }
                /****
                 * 'xlsxToHtml' =>
                 * function (): void {
                 * AppController::readXLSXWriteHTML();
                 * },
                 * 'indexView' =>
                 * function () {
                 * return AppController::index();
                 * },
                 * 'insertToXlsx' =>
                 * function (): string {
                 * return json_encode(AppController::insert(), JSON_UNESCAPED_UNICODE);
                 * },
                 * 'consiliar' =>
                 * function () {
                 * return json_encode(AppController::consiliar(), JSON_UNESCAPED_UNICODE);
                 * },
                 * ,
                 * 'test' =>
                 * function () {
                 * var_dump(AppController::listTableToJson());
                 * },
                 */
            );
    }


    /**
     * @param string $closureIndex
     * @return mixed
     */
    public function get(string $closureIndex)
    {
        if (!Helpers::stringIsOk($closureIndex)) {
            Throw new InvalidArgumentException("Miss the closureIndex");
        }
        return $this->routes[$closureIndex];
    }

    /**
     * Debugger
     */
    public function debugger()
    {
        var_dump($this->routes);
    }
}
