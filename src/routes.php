<?php

use Slim\Http\Request;
use Slim\Http\Response;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\DatabasePresenceVerifier;
use Illuminate\Validation\Factory;
use Firebase\JWT\JWT;

require __DIR__ . '/../src/models/Cadastro.php';

// Routes
/*$app->get('/[{name}]', function (Request $request, Response $response, array $args) {
    $this->logger->info("Slim-Skeleton '/' route");
    return $this->renderer->render($response, 'index.phtml', $args);
});*/

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

$app->get('/tipousoimovel', function (Request $request, Response $response) {
    $query = Capsule::table('VW_TIPOUSO_IMOVEL')
        ->select(['ID','DESCRICAO']);

    $return = $query->get();
    return $response->withJson( $return );
});

// Método para identificar o contribuinte na base de dados do STM
$app->post('/identificar', function (Request $request, Response $response){

    $return = array('success'=>0);
    $data = $request->getParsedBody();
    
    $matricula = preg_replace('/[^0-9]/', '', $data['MATRICULA_IPTU']);
    $cpfcnpj = preg_replace('/[^0-9]/', '', $data['CPFCNPJ']);

    $query = Capsule::table('VW_CADASTRO_STM')
        ->select('MATRICULA, CPFCNPJ')
        ->where([
            ['MATRICULA', '=', $matricula],
            ['CPFCNPJ', '=', $cpfcnpj]
    ]);

    $resultado = $query->count();

    if($resultado > 0){
        $return['success'] = 1;
    }else{
        $return['msgErro'] = 'Registro(s) com estes dados não encontrado(s).';
    }

    return $response->withJson( $return );

});

// Método para cadastrar o contribuinte na base de dados
$app->post('/cadastrar', function (Request $request, Response $response){
    $return = array('success'=>0);
    $data = $request->getParsedBody();

    $matricula = preg_replace('/[^0-9]/', '', $data['MATRICULA_IPTU']);
    $cpfcnpj = preg_replace('/[^0-9]/', '', $data['CPFCNPJ']);
    // Ou utilizar o id
    $cadastrado = Cadastro::where([
        ['CPFCNPJ', '=', $cpfcnpj],
        ['MATRICULA_IPTU', '=', $matricula],
        ['ANO', '=', date('Y')]
    ])->first();

    /* Necessário para instanciar a validação */
    $loader = new FileLoader(new Filesystem, 'lang');
    $translator = new Translator($loader, 'en');
    $validation = new Factory($translator, new Container);

    try{
        if(!$cadastrado){
            $cadastro = new Cadastro;
            $cadastro->ANO = date('Y');
        }else{
            $cadastro = $cadastrado;
        }

        $cadastro->MATRICULA_IPTU = $matricula;
        $cadastro->CPFCNPJ = $cpfcnpj;
        $cadastro->NOME_DECLARANTE = $data['NOME_DECLARANTE'];
        $cadastro->CPF_DECLARANTE = preg_replace('/[^0-9]/', '', $data['CPF_DECLARANTE']);
        $cadastro->FAIXA_GERACAO = $data['FAIXA_GERACAO'];
        $cadastro->TIPO_USO = $data['TIPO_USO'];
        $cadastro->QTD_PESSOAS = $data['QTD_PESSOAS'];
        $cadastro->EMAIL = $data['EMAIL'];
        $cadastro->TELEFONE = $data['TELEFONE'];
        $cadastro->LOGRADOURO = $data['LOGRADOURO'];
        $cadastro->COMPLEMENTO = $data['COMPLEMENTO'];
        $cadastro->NUMERO = $data['NUMERO'];
        $cadastro->BAIRRO = $data['BAIRRO'];
        $cadastro->CEP = $data['CEP'];
        $cadastro->CIDADE = $data['CIDADE'];
        
        
        $validator = $validation->make($cadastro->getAttributes(), $cadastro->rules, $cadastro->messages);
        if ($validator->fails()) {
            $return['errors'] = $validator->errors(); 
        }else{
            $cadastro->save();
            $return['success'] = 1;
        }
        
    }catch(Exception $e){
        $return['errors'] = $e->getMessage();
    }
    return $response->withJson( $return );

});


/*
   Método para consultar o contribuinte na base de dados
 - Verifica se o contribuinte já tem cadastro (do ano corrente ou ano anterior) na base de dados do TRSD,
   caso não tenha, pega os dados do STM para um novo cadastro.
 */

$app->post('/consultar', function (Request $request, Response $response){
    $data = $request->getParsedBody();
    $matricula = preg_replace('/[^0-9]/', '', $data['MATRICULA_IPTU']);
    $cpfcnpj = preg_replace('/[^0-9]/', '', $data['CPFCNPJ']);
    
    $cadastrado = Cadastro::where([
        ['CPFCNPJ', '=', $cpfcnpj],
        ['MATRICULA_IPTU', '=', $matricula]
    ])->where(function($q){
        $q->where('ANO', date('Y'))
        ->orWhere('ANO', date('Y')-1);
    })->first();

    $return = null;

    //Se encontra não registro na base de dados do TRSD, retorna dados do STM 
    if($cadastrado){
        $return = $cadastrado->toArray();
    }else{
        $query = Capsule::table('VW_CADASTRO_STM')
            ->select(['MATRICULA', 'CPFCNPJ', 'LOGRADOURO', 'TIPOLOGRADOURO', 'COMPLEMENTO', 'NUMERO', 'BAIRRO', 'CEP', 'CIDADE','TIPO_USO'])
            ->where([
            ['MATRICULA', '=', $matricula],
            ['CPFCNPJ', '=', $cpf]
        ]);

        $return = $query->get()[0];
    }

    return $response->withJson( $return );

});