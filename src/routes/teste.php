<?php 

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Illuminate\Database\Capsule\Manager as Capsule;

// Recebe uma requisição POST contendo dados em formato json (ou não)
$app->get('/teste/{name}', function (Request $request, Response $response, $args){
    
    $data = $request->getParsedBody();
    $users = Capsule::table('pco_usuario')->get();
    return $response->withJson($users );

});