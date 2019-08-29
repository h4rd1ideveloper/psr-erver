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
  $body->write(
    /**@lang HTML */
    sprintf(
      '
            <!doctype html>
            <html lang="pt-br">
              <head>
                <!-- Required meta tags -->
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
            
                <!-- Bootstrap CSS -->
                <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
            
                <title>%s</title>
              </head>
              <body>
              <!-- As a link -->
                <nav class="navbar navbar-light bg-light mb-5">
                  <a class="navbar-brand" href="#">%s</a>
                </nav>
                <main class="container">
                   <div class="row">
                        <div class="col-8 mx-auto mt-5">
                            <table class="table table-borderless table-striped table-hover">
                                <thead>
                                    <tr>
                                      <th scope="col">#</th>
                                      <th scope="col">First</th>
                                      <th scope="col">Last</th>
                                      <th scope="col">Handle</th>
                                    </tr>
                                  </thead>
                                <tbody>
                                <tr>
                                  <th scope="row">1</th>
                                  <td>Mark</td>
                                  <td>Otto</td>
                                  <td>@mdo</td>
                                </tr>
                                <tr>
                                  <th scope="row">2</th>
                                  <td>Jacob</td>
                                  <td>Thornton</td>
                                  <td>@fat</td>
                                </tr>
                                <tr>
                                  <th scope="row">3</th>
                                  <td colspan="2">Larry the Bird</td>
                                  <td>@twitter</td>
                                </tr>
                              </tbody>
                            </table>
                        </div>
                    </div>
                </main>
                <!-- Optional JavaScript -->
                <!-- jQuery first, then Popper.js, then Bootstrap JS -->
                <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
                <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
                <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
              </body>
            </html>
            ',
      $dados['title'] ?? 'ola mundo',
      $dados['title'] ?? 'ola mundo'
    )
  );
  return $res->withBody($body)
    ->withHeader("Content-Type", "text/html")
    ->getBody();
});
$app->get('/test', function (Request $req, Response $res) {
  return $res->withHeader("Content-Type", "application/json")->withBody($req->getBody())->getBody();
});

$app->run();
