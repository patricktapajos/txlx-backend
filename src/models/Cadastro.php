<?php 
	class Cadastro extends Illuminate\Database\Eloquent\Model {
		protected $table = "trsd_cadastro";
		public $rules = [
			'cad_cpf' => 'required',
			'cad_nome' => 'required',
		];
		protected $fillable = ['cad_cpf','cad_nome'];
	}