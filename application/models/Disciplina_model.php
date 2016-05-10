<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Disciplina_model extends CI_Model {
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

	/**
	 * Popular
	 *
	 * Preenche as propriedades da instância com valores obtidos na base a partir do ID
	 * Retorna esta própria instância para permitir concatenação de funções ou null se não houver ID definido
	 * @return Disciplina_model
	 */
	public function popular($apenas_estrutura = false)
	{
		if (isset($this->id))
		{
			$this->load->helper('class_helper');

			$dados_instancia = $this->db->where('id', $this->id)->get('disciplinas')->row();

			if (!isset($this->nome))
			{
				$this->nome = $dados_instancia->nome;
			}
			if (!isset($this->denominacao_bloco))
			{
				$this->denominacao_bloco = $dados_instancia->denominacao_bloco;
			}

			if (!isset($this->bloco))
			{
				carregar_classe('models/Bloco_model');
				$this->bloco = new Bloco_model(array('id' => $dados_instancia->id_bloco));
			}
			$this->bloco->popular($apenas_estrutura);

			return $this;
		}
		else
		{
			return null;
		}
	}

}
