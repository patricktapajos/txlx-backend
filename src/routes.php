<?php

use Slim\Http\Request;
use Slim\Http\Response;
use Firebase\JWT\JWT;

// Routes
$app->get('/informacoes', function (Request $request, Response $response, array $args) {
    $args['url'] = env('URL_CLIENT_DEFAULT', '');
    return $this->renderer->render($response, 'index.phtml', $args);
});

$app->get('/authtrsdtoken', function (Request $request, Response $response) use ($app) {
    $key = env('JWT_KEY', '');
    $token = array(
        "user" => "@trsd",
        "github" => "https://gitlab.manaus.am.gov.br/SEMEF/gesisp/sistemas/txlx-backend.git"
    );
    $jwt = JWT::encode($token, $key);
    return $response->withJson(["auth-jwt" => $jwt], 200)
        ->withHeader('Content-type', 'application/json');   
});

$app->get('/tipousoimovel', \CadastroController::class . ':tipousoimovel');

$app->get('/geracaoresiduos/{TIPO_DOMICILIO}', \CadastroController::class . ':geracaoresiduos');

$app->post('/identificar', \CadastroController::class . ':identificar');

$app->post('/cadastrar', \CadastroController::class . ':cadastrar');

$app->post('/consultar', \CadastroController::class . ':consultar');

$app->post('/visualizarDados', \CadastroController::class . ':visualizarDados');