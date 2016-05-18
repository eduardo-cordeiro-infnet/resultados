<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Bloco_model extends CI_Model {
	public $id;
	public $nome;

	private $populando = false;

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
	public function popular()
	{
		if (isset($this->id))
		{
			if (!$this->populando)
			{
				$this->populando = true;

				$dados_instancia = $this->db->get_where('blocos', array('id' => $this->id))->row();

				if (!isset($this->nome))
				{
					$this->nome = $dados_instancia->nome;
				}

				$this->populando = false;
			}

			return $this;
		}
		else
		{
			return null;
		}
	}

}
