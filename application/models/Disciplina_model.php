<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Disciplina_model {
	public $id;
	public $nome;
	public $denominacao_bloco;
	public $bloco;

	public function __construct($param = null)
	{
		if (is_array($param))
		{
			if (isset($param['id']))
			{
				$this->id = $param['id'];
			}
			if (isset($param['nome']))
			{
				$this->nome = $param['nome'];
			}
			if (isset($param['denominacao_bloco']))
			{
				$this->denominacao_bloco = $param['denominacao_bloco'];
			}
			if (isset($param['bloco']))
			{
				$this->bloco = $param['bloco'];
			}
		}
		else if (isset($param))
		{
			$this->id = $param;
		}
	}

}
