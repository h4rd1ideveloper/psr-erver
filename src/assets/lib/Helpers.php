<?php

namespace App\assets\lib;
use phpDocumentor\Reflection\Types\Boolean;

/**
 * Class Helpers
 * @author Yan Santos Policarpo
 * @version 1.1.0
 * @todo  Doc every methods
 */
class Helpers
{

    /**
     * Helpers constructor.
     */
    public static function showErros()
    {
        error_reporting(E_ALL | E_STRICT);
        ini_set('max_execution_time', '600000');
    }

    /**
     *
     */
    public static function const()
    {
        define('PRODUCTION_DB_NAME', getenv('PRODUCTION_DB_NAME'));
        define('PRODUCTION_DB_USER', getenv('PRODUCTION_DB_USER'));
        define('PRODUCTION_DB_PASS', getenv('PRODUCTION_DB_PASS'));
        define('PRODUCTION_DB_TYPE', getenv('PRODUCTION_DB_TYPE'));
        define('PRODUCTION_DB_HOST', getenv('PRODUCTION_DB_HOST'));
        define('ENEL_FIELDS', getenv('ENEL_FIELDS'));
        define('ENEL_TABLE', getenv('ENEL_TABLE'));
        //Dev const
        define("DB_type", "mysql");
        define("DB_HOST", "localhost");
        define("DB_USER", "root");
        define("DB_PASS", "");
        define("DB_NAME", "crefazscm_webscm");
    }

    /**
     * @param $source
     * @return array
     */
    public static function formmattSource($source)
    {
        $formatted_source = [];
        $source = array_filter($source, function ($value) {
            return is_array($value);
        });
        for ($k = 0; $k < count($source); $k++) {
            //Skip Header
            if ($k != 0) {
                //Fix keys and values from source to formatted_source
                for ($i = 0; $i < count($source[0]); $i++) {
                    if ($source[0][$i] !== null && $source[$k][$i] !== null) { // skip empty
                        $formatted_source[$k][$source[0][$i]] = $source[$k][$i];
                    }
                }
            }
        }
        return $formatted_source;
    }

    /**
     * @param $formatted_source
     * @param $fields
     * @return mixed
     */
    public static function toVerify($formatted_source, $fields)
    {
        for ($index = 1; $index <= count($formatted_source); $index++) {
            //Fix key and values of the array to verify
            foreach ($fields as $key => $value) {
                isset($formatted_source[$index][$value]) && $toVerify[($index - 1)][$key] = Helpers::formatValueByKey($formatted_source[$index][$value], $key);
            }
        }
        return $toVerify;
    }

    /**
     * formatValueByKey
     * @param $value
     * @param $key
     * @return mixed|string
     */
    public static function formatValueByKey($value, $key)
    {
        switch ($key) {
            case 'correlativoDocumento':
            {
                return str_pad($value, 3, "0", STR_PAD_LEFT);
                break;
            }
            case 'valor':
            {
                return str_replace('k', '.', str_replace(',', '.', $value));
                break;
            }
            case 'codProduto':
            {
                return strlen($value) > 2 ? $value : str_pad($value, 2, "0", STR_PAD_LEFT);
                break;
            }
            case 'dataOcorrencia':
            case 'dataBaixaPagamento':
            case 'dataPagamento':
            {
                $value = str_replace('/', '-', $value);
                $date = explode('-', $value);
                $y = (strlen($date[0]) === 4) ? $date[0] : str_pad($date[0], 4, "20", STR_PAD_LEFT);
                $mm = (strlen($date[1]) === 2) ? $date[1] : str_pad($date[1], 2, "0", STR_PAD_LEFT);
                $dd = (strlen($date[2]) === 2) ? $date[2] : str_pad($date[2], 2, "0", STR_PAD_LEFT);
                return sprintf('%s-%s-%s', $y, $mm, $dd);
                break;
            }
            default :
            {
                return $value;
                break;
            }
        }
    }

    /**
     * @param $toJson
     * @return string
     */
    public static function toJson($toJson): string
    {
        return json_encode($toJson, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param array $OBJ
     * @return array
     */
    public static function objectKeys(array $OBJ): array
    {
        $arr = [];
        foreach ($OBJ as $key => $valueNotUsedHer) {
            self::insertIfNotExist($key, $arr);
        }
        return $arr;
    }

    /**
     * @param $value
     * @param $arr
     */
    public static function insertIfNotExist($value, & $arr)
    {
        if (!in_array($value, $arr)) {
            $arr[] = $value;
        }
    }

    /**
     * @param array $ids
     * @param $arr
     * @return array
     */
    public static function getRowsById(array $ids, $arr): array
    {
        $source = [];
        foreach ($ids as $id) {
            isset($arr[$id]) && $source[] = $arr[$id];
        }
        return $source;
    }

    /**
     * @param array $ids
     * @param array $headers
     * @param array $source
     * @return array
     */
    public static function countDates(array $ids, array $headers, array $source)
    {
        $label = '';
        foreach ($headers as $header) {
            if (
                $header === 'dataBaixaPagamento' ||
                $header === 'dataOcorrencia' ||
                $header === 'dataPagamento'
            ) {
                $label = $header;
                break;
            }
            continue;
        }
        $dates = [];
        foreach ($ids as $id) {
            $dates[$source[$id][$label]] = ($dates[$source[$id][$label]] ?? 0) + 1;
        }
        return $dates;
    }

    /**
     * Init Headers
     */
    public static function cors()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: *");
        header("Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS");
        header("Accept: application/json, application/x-www-form-urlencoded, multipart/form-data, application/xhtml+xml, application/xml;q=0.9, multipart/*, text/plain, text/html,  image/webp, */*;q=0.8");
        header("Accept-Encoding: compress, gzip");//

    }

    /**
     * @param string|null $headerContent
     */
    public static function setHeader(string $headerContent = 'Content-Type: application/json')
    {
        header(sprintf("%s", $headerContent));
    }

    /**
     * @param $param
     * @return array
     */
    public static function formatArray($param)
    {
        $newArray = [];
        foreach ($param as $key => $value) {
            $newArray[$key] = self::formatValueByKey($value, $key);
        }
        return $newArray;
    }

    public static function stringIsOk(string $string): bool
    {
    return ! ($string === null || empty($string) || !isset($string) || $string === "" );
    }
    /**
     * @param $data
     * @return mixed
     */
    public static function jsonToArray($data)
    {
        return json_decode($data, true);
    }
}