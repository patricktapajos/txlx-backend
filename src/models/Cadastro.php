<?php 
	class Cadastro extends Illuminate\Database\Eloquent\Model {
		protected $table = "TRSD_CADASTRO";
		public $timestamps = false;
		public $rules = [
			'MATRICULA_IPTU' => 'required',
			'CPFCNPJ' => 'required',
			'NOME_DECLARANTE' => 'required',
			'CPF_DECLARANTE' => 'required',
			'FAIXA_GERACAO' => 'required',
			'TIPO_USO' => 'required',
			'QTD_PESSOAS' => 'required'
		];
		public $messages = [
			'MATRICULA_IPTU.required' => 'Matrícula obrigatória',
			'CPFCNPJ.required' => 'CPF do Contribuinte obrigratório',
			'NOME_DECLARANTE.required' => 'Nome do Declarante obrigatório',
			'CPF_DECLARANTE.required' => 'CPF do Declarante obrigatório',
			'FAIXA_GERACAO.required' => 'Faixa de Geração obrigatória',
			'TIPO_USO.required' => 'Tipo de Uso obrigatório',
			'QTD_PESSOAS.required' => 'Qauntidade de Pessoas obrigatória'
		];
		protected $fillable = [
			'MATRICULA_IPTU',
			'CPFCNPJ',
			'NOME_DECLARANTE',
			'CPF_DECLARANTE',
			'FAIXA_GERACAO',
			'TIPO_USO',
			'QTD_PESSOAS',
			'EMAIL',
			'TELEFONE',
			'LOGRADOURO',
			'COMPLEMENTO',
			'NUMERO',
			'BAIRRO',
			'CEP',
			'CIDADE',
			'DATA_CADASTRO',
			'ANO',
			'CODIGO_COMPROVANTE'
		];

		protected $casts = [
			'MATRICULA_IPTU'=>'int',
			'CPF'=>'string',
			'NOME_DECLARANTE'=>'string',
			'CPF_DECLARANTE'=>'string',
			'FAIXA_GERACAO'=>'string',
			'TIPO_USO'=>'string',
			'QTD_PESSOAS'=>'string',
			'EMAIL'=>'string',
			'TELEFONE'=>'string',
			'LOGRADOURO'=>'string',
			'COMPLEMENTO'=>'string',
			'NUMERO'=>'int',
			'BAIRRO'=>'string',
			'CEP'=>'string',
			'CIDADE'=>'string',
			'DATA_CADASTRO',
			'ANO'=>'int',
			'CODIGO_COMPROVANTE'=>'string'
		];
	}