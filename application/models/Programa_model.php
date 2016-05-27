<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Programa_model {
	public $id;
	public $nome;
	public $sigla;

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
			if (isset($param['sigla']))
			{
				$this->sigla = $param['sigla'];
			}
		}
		else if (isset($param))
		{
			$this->id = $param;
		}
	}

	public function __toString()
	{
		return $this->nome;
	}

}
