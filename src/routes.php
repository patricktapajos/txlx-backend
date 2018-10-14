<?php

use Slim\Http\Request;
use Slim\Http\Response;
use Illuminate\Database\Capsule\Manager as Capsule;
require '/models/Cadastro.php';

// Routes
$app->get('/[{name}]', function (Request $request, Response $response, array $args) {
    $this->logger->info("Slim-Skeleton '/' route");
    return $this->renderer->render($response, 'index.phtml', $args);
});

// Método para identificar o contribuinte na base de dados do STM
$app->post('/identificar', function (Request $request, Response $response){
    /*
        // Get the PDO object
        $pdo = $this->db->getPdo();
        // Use prepared statements
        $statement = $pdo->prepare("SELECT * FROM users WHERE id= :id");
        $statement->execute(['id' => 1]);
        $userRow = $statement->fetch();
    */
    $return = array('success'=>0);
    $data = $request->getParsedBody();
    
    $numero = preg_replace('/[^0-9]/', '', $data['matricula']);
    $cpf = preg_replace('/[^0-9]/', '', $data['cpf']);

    $query = Capsule::table('VW_MATRICULA')
        ->select('NUMERO','CPF')
        ->where([
           ['NUMERO', '=', $numero],
           ['CPF', '=', $cpf]
        ]);

    $resultado = $query->count();

    if($resultado > 0){
        $return['success'] = 1;
    }else{
        $return['msgErro'] = 'Registro(s) com estes dados não encontrado(s).';
    }

    return $response->withJson( $return );

});

// Método para cadastear o contribuinte na base de dados
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
