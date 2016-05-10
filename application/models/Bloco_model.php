<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Bloco_model extends CI_Model {
	public $id;
	public $nome;

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
		}
		else if (isset($param))
		{
			$this->id = $param;
		}
	}

	/**
	 * Popular
	 *
	 * Preenche as propriedades da instância com valores obtidos na base a partir do ID
	 * Retorna esta própria instância para permitir concatenação de funções ou null se não houver ID definido
	 * @return Bloco_model
	 */
	public function popular($apenas_estrutura = false)
	{
		if (isset($this->id))
		{
			$dados_instancia = $this->db->where('id', $this->id)->get('blocos')->row();

			if (!isset($this->nome))
			{
				$this->nome = $dados_instancia->nome;
			}

			return $this;
		}
		else
		{
			return null;
		}
	}

}
