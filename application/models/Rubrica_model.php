<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Rubrica_model {
	public $mdl_id;
	public $descricao;
	public $ordem;
	public $subcompetencias = array();

	public function __construct($dados = null)
	{
		if (is_array($dados))
		{
			$this->mdl_id = $dados['mdl_id'];
			$this->descricao = $dados['descricao'];
			$this->ordem = $dados['ordem'];
		}
	}

}
