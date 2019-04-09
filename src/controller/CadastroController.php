<?php 

namespace application\controller;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\DatabasePresenceVerifier;
use Illuminate\Validation\Factory;
use application\models\Cadastro;

class CadastroController {
    
    public function tipoUsoImovel($request, $response, $args){
        $query = Capsule::table('VW_TIPOUSO_IMOVEL')
            ->select(['ID','DESCRICAO']);
        $return = $query->get();
        return $response->withJson( $return );
    }

    public function geracaoResiduos($request, $response, $args){
        
        $opcoes = [];
        /* Tipo 2 é código para RESIDENCIAL */
        if($args['TIPO_DOMICILIO'] == 2){
            $opcoes = [
                ['id'=>'UGRESPECIAL', 'descricao'=>'0 a 10 litros de resíduos por dia'],
                ['id'=>'UGR1', 'descricao'=>'10 a 20 litros de resíduos por dia'],
                ['id'=>'UGR2', 'descricao'=>'20 a 30 litros de resíduos por dia'],
                ['id'=>'UGR3', 'descricao'=>'30 a 60 litros de resíduos por dia'],
                ['id'=>'UGR4', 'descricao'=>'60 litros ou mais de resíduos por dia'],
            ];
        }else{
            $opcoes = [
                ['id'=>'URG0','descricao'=>'Não gerador de Resíduos'],
                ['id'=>'UGR1', 'descricao'=>'30 litros de resíduos por dia'],
                ['id'=>'UGR2', 'descricao'=>'30 a 60 litros de resíduos por dia'],
                ['id'=>'UGR3', 'descricao'=>'60 a 100 litros de resíduos por dia'],
                ['id'=>'UGR4', 'descricao'=>'100 a 200 litros de resíduos por dia'],
            ];
        }
        return $response->withJson( $opcoes );
    }

    // Método para identificar o contribuinte na base de dados do STM
    public function identificar($request, $response, $args){
        
        $return = array('success'=>0);
        $data = $request->getParsedBody();
        $matricula = preg_replace('/[^0-9]/', '', $data['MATRICULA_IPTU']);
        $cpfcnpj = preg_replace('/[^0-9]/', '', $data['CPFCNPJ']);
        $query = Capsule::table('VW_CADASTRO_STM')
            ->select('MATRICULA','CPFCNPJ')
            ->where('MATRICULA', '=', $matricula);
        $resultado = $query->get()[0];

        if(count($resultado) == 0){
            $return['msgErro'] = 'Registro(s) com estes dados não encontrado(s).';
        }else{
            if($resultado->cpfcnpj != $cpfcnpj){
                $return['success'] = 0;
                $return['msgErro'] = 
                    '<p>Este imóvel está vinculado a outro proprietário. Caso deseje alterar a titularidade clique no link: </p> <p><a target="_blank" href="https://semefatende.manaus.am.gov.br/inventario.php?id=130">Alteração de Titularidade</a></p>';
            }else{
                $return['success'] = 1;
            }
        }
        return $response->withJson( $return );
    }

    // Método para cadastrar o contribuinte na base de dados
    public function cadastrar($request, $response, $args){

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
    }

    /*
        Método para consultar o contribuinte na base de dados
        - Verifica se o contribuinte já tem cadastro (do ano corrente ou ano anterior) na base de dados do TRSD,
        caso não tenha, pega os dados do STM para um novo cadastro.
    */

    public function consultar($request, $response, $args){

        $data = $request->getParsedBody();
        $matricula = preg_replace('/[^0-9]/', '', $data['MATRICULA_IPTU']);
        $cpfcnpj = preg_replace('/[^0-9]/', '', $data['CPFCNPJ']);

        if($data['ANO']){
            $cadastrado = Cadastro::where([
                ['CPFCNPJ', '=', $cpfcnpj],
                ['MATRICULA_IPTU', '=', $matricula],
                ['ANO', '=', $data['ANO']],
            ])->first();
        }else{
            $cadastrado = Cadastro::where([
                ['CPFCNPJ', '=', $cpfcnpj],
                ['MATRICULA_IPTU', '=', $matricula]
            ])->where(function($q){
                $q->where('ANO', date('Y'))
                ->orWhere('ANO', date('Y')-1);
            })->orderBy('id', 'DESC')->first();
        }
              

        $return = null;

        //Se encontra não registro na base de dados do TRSD, retorna dados do STM 
        if($cadastrado){
            $return = $cadastrado->toArray();
        }else{
            $query = Capsule::table('VW_CADASTRO_STM')
                ->select(['MATRICULA', 'CPFCNPJ', 'LOGRADOURO', 'TIPOLOGRADOURO', 'COMPLEMENTO', 'NUMERO', 'BAIRRO', 'CEP', 'CIDADE','TIPO_USO'])
                ->where([
                ['MATRICULA', '=', $matricula],
                ['CPFCNPJ', '=', $cpfcnpj]
            ]);

            $return = $query->get()[0];
        }

        return $response->withJson( $return );

    }

    
    /*
    Método para visualizar os dados cadastrados do contribuinte na base de dados
    */

    public function visualizarDados($request, $response, $args){
        $data = $request->getParsedBody();
        $matricula = preg_replace('/[^0-9]/', '', $data['MATRICULA_IPTU']);
        $cpfcnpj = preg_replace('/[^0-9]/', '', $data['CPFCNPJ']);
                
       
        if($data['ANO']){
            $cadastrado = Cadastro::where([
                ['CPFCNPJ', '=', $cpfcnpj],
                ['MATRICULA_IPTU', '=', $matricula],
                ['ANO', '=', $data['ANO']],
            ])->first();
        }else{
            $cadastrado = Cadastro::where([
                ['CPFCNPJ', '=', $cpfcnpj],
                ['MATRICULA_IPTU', '=', $matricula]
            ])->where(function($q){
                $q->where('ANO', date('Y'))
                ->orWhere('ANO', date('Y')-1);
            })->orderBy('id', 'DESC')->first();
        }
    
        $return = null;
    
        //Se encontra não registro na base de dados do TRSD, retorna dados do STM 
        if($cadastrado){
            $return = $cadastrado->toArray();
    
            if($return['tipo_uso'] == 2){
                $opcoes = [
                    'UGRESPECIAL'=>'Geração de 0 a 10 litros de resíduos por dia',
                    'UGR1'=>'Geração de 10 a 20 litros de resíduos por dia',
                    'UGR2'=>'Geração de 20 a 30 litros de resíduos por dia',
                    'UGR3'=>'Geração de 30 a 60 litros de resíduos por dia',
                    'UGR4'=>'Geração de 60 litros ou mais de resíduos por dia',
                ];
            }else{
                $opcoes = [
                    'URG0'=>'Não gerador de Resíduos',                
                    'UGR1'=>'Geração de 0 a 30 litros de resíduos por dia',
                    'UGR2'=>'Geração de 30 a 60 litros de resíduos por dia',
                    'UGR3'=>'Geração de 60 a 100 litros de resíduos por dia',
                    'UGR4'=>'Geração de 100 a 200 litros de resíduos por dia',
                ];
            }
    
            $return['faixa_geracao'] = $opcoes[$return['faixa_geracao']];
    
            if($return['tipo_uso'] == 2){
                $return['tipo_uso'] = 'Residencial';
            }else{
                $return['tipo_uso'] = 'Não Residencial';
            }
        }
    
        return $response->withJson( $return );
        
    }
}