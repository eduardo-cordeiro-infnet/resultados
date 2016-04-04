<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Estudante_model {
	public $mdl_userid;
	public $nome_completo;

	private $email;
	private $mdl_username;

	public function __construct($dados = null)
	{
		if (is_array($dados))
		{
			$this->nome_completo = $dados['nome_completo'];
			$this->email = $dados['email'];
			$this->mdl_username = $dados['mdl_username'];
			$this->mdl_userid = $dados['mdl_userid'];
		}
	}
}
