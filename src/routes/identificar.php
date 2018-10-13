<?php 

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Illuminate\Database\Capsule\Manager as Capsule;

// Recebe uma requisição POST contendo dados em formato json (ou não)
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
        $return['msg'] = 'Registro(s) com estes dados não encontrado(s).';
    }

    return $response->withJson( $return );

});