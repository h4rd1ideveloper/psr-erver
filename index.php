<?php
//declare(strict_types=1); only php-7
require_once __DIR__ . '/vendor/autoload.php';

/***
 * Setup config
 */

use App\assets\lib\Helpers;
use App\routes\Dispatch;
use App\routes\Router;
use App\http\Request;
use App\http\Response;

Helpers::showErros();
//Helpers::cors();
Helpers::const();
$app = new Router();
$Dispatch = new Dispatch();
/**
 * Declare routes with closures her
 */
$app->get('/', function (Request $req, Response $res) {
  $dados = Helpers::jsonToArray($req->getBody());
  $body = $res->getBody();
  $body->write('ok');
  return $res->withBody($body)
    ->withHeader("Content-Type", "text/html")
    ->getBody();
});
$app->get('/test', function (Request $req, Response $res) {
  return "ok"; //$res->withHeader("Content-Type", "application/json")->withBody($req->getBody())->getBody();
});
$app->debugger();
$app->run();
