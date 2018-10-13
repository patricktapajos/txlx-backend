<?php 
// Route for login
require '../models/Cadastro.php';
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// Recebe uma requisição POST contendo dados em formato json (ou não)
$app->post('/cadastrar', function (Request $request, Response $response){
    $return = array('success'=>0);
    $data = $request->getParsedBody();
    $cadastrado = Cadastro::where('cad_cpf', '=', $data['cpf'])->first();

    if(!$cadastrado){
        //insere
        try{
            $cadastro = new Cadastro;
            $validator = Validator::make($request->all(), [
                $cadastro->rules
           ]);
           if ($validator->fails()) {
                $return['errors'] = $validator->errors;
           }
            $cadastro->save();
            $return['success'] = 1;
        }catch(Exception $e){
            $return['errors'] = $e->getMessage();
        }
    }

    return $response->withJson( $return );

});