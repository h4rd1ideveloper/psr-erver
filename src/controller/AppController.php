<?php

namespace App\controller;

use App\assets\lib\Helpers;
use App\model\XLSXToHtmlParse;

/**
 * Class AppController
 * @see Controller
 */
final class AppController extends Controller
{
    /** Not Used in actual context
     * public static function index()
     * {
     * Helpers::setHeader("Content-Type: text/html");
     * self::view('index');
     * }
     *
     * public static function consiliar()
     * {
     * self::view('result', (new XLSXToHtmlParse(true))->checkInTable(parent::request()));
     * }
     *
     * public static function insert()
     * {
     * return (new XLSXToHtmlParse())->XLSXinsert(parent::request());
     * }
     *
     * public static function readXLSXWriteHTML(): void
     * {
     * self::view('list',
     * (new XLSXToHtmlParse(true))->XLSXtoJSON($_FILES["fileToUpload"]["tmp_name"])
     * );
     * }
     *
     * public static function listTableToJson()
     * {
     * return (new XLSXToHtmlParse(true))->listTable();
     * }
     */
    public static function enelFields()
    {
        Helpers::setHeader();
        return Helpers::toJson((new XLSXToHtmlParse(true))->fieldsEnel());
    }

    /**
     * @return string
     */
    public static function check()
    {
        Helpers::setHeader();
        return Helpers::toJson((new XLSXToHtmlParse(true))->checkInTable_before(parent::request()));
    }

    /**
     * @return string
     */
    public static function checkCount()
    {
        Helpers::setHeader();
        return Helpers::toJson(
            Helpers::countDates(
                parent::request()['ids'],
                parent::request()['headers'],
                parent::request()['xlsx']
            )
        );
    }

    /**
     * @return string
     */
    public static function test()
    {
        Helpers::setHeader();
        return Helpers::toJson(array("raw" => parent::request(), "body" => parent::getRequestBody(), "GET"=>$_GET,"POST"=>$_POST,"FILE"=>$_FILES));
    }
}