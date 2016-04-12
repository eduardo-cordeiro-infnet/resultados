<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Competencia_model {
	public $codigo;
	public $nome;
	public $subcompetencias = array();

	public function __construct($dados = null)
	{
		if (is_array($dados))
		{
			$this->codigo = $dados['codigo'];
			$this->nome = $dados['nome'];
		}
	}

}
