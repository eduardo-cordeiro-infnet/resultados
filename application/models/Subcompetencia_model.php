<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Subcompetencia_model {
	public $codigo_completo;
	public $nome;
	public $obrigatoria;

	public function __construct($dados = null)
	{
		if (is_array($dados))
		{
			$this->codigo_completo = $dados['codigo_completo'];
			$this->nome = $dados['nome'];
			$this->obrigatoria = $dados['obrigatoria'];
		}
	}

	/**
	 * Obter código sem obrigatoriedade
	 *
	 * Retorna o código completo da subcompetência sem asterisco,
	 * mesmo que seja obrigatória
	 * @return string
	 */
	public function obter_codigo_sem_obrigatoriedade()
	{
		return str_replace(SUBCOMPETENCIA_SIMBOLO_OBRIGATORIEDADE, '', $this->codigo_completo);
	}

	/**
	 * Obter código da competência
	 *
	 * Retorna o código da competência da qual esta subcompetência faz parte
	 * @return string
	 */
	public function obter_codigo_competencia()
	{
		return explode('.', $this->codigo_completo)[0];
	}

}
