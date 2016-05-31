<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Rubrica_model extends CI_Model {
	public $mdl_id;
	public $descricao;
	public $ordem;
	public $avaliacao;

	public $subcompetencias = array();

	private $populando = false;

	public function __construct($param = null)
	{
		if (is_array($param))
		{
			if (isset($param['mdl_id']))
			{
				$this->mdl_id = $param['mdl_id'];
			}
			if (isset($param['descricao']))
			{
				$this->descricao = $param['descricao'];
			}
			if (isset($param['ordem']))
			{
				$this->ordem = $param['ordem'];
			}
			if (isset($param['avaliacao']))
			{
				$this->avaliacao = $param['avaliacao'];
			}
		}
		else if (isset($param))
		{
			$this->mdl_id = $param;
		}

		$this->load->database();
	}

	/**
	 * Popular
	 *
	 * Preenche as propriedades da instância com valores obtidos na base a partir do ID da rubrica no Moodle
	 * Retorna esta própria instância para permitir concatenação de funções ou null se não houver ID definido
	 * @return Rubrica_model
	 */
	public function popular($apenas_estrutura = false)
	{
		if (isset($this->mdl_id))
		{
			if (!$this->populando)
			{
				$this->populando = true;

				$dados_instancia = $this->db->get_where('v_rubricas_avaliacoes', array('id_mdl_gradingform_rubric_criteria' => $this->mdl_id))->row();

				if (!isset($this->descricao))
				{
					$this->descricao = $dados_instancia->rubrica;
				}
				if (!isset($this->ordem))
				{
					$this->ordem = $dados_instancia->ordem_rubrica;
				}

				if (empty($this->subcompetencias))
				{
					$this->popular_subcompetencias($apenas_estrutura);
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

	/**
	 * Popular subcompetências
	 *
	 * Preenche as propriedades da instância referentes a subcompetências associadas à rubrica
	 */
	public function popular_subcompetencias($apenas_estrutura = false)
	{
		$this->load->helper('obj_array_helper');

		carregar_classe('models/Subcompetencia_model');

		$id_subcompetencias_db = obj_array_map_prop(
			$this->db->get_where('subcompetencias_mdl_gradingform_rubric_criteria', array('id_mdl_gradingform_rubric_criteria' => $this->mdl_id))->result(),
			'id_subcompetencia'
		);
		$id_subcompetencias_nao_instanciadas = array_diff($id_subcompetencias_db, obj_array_map_id($this->subcompetencias));

		// Inclui em $this->subcompetencias todas as subcompetências que estão na base mas não instanciadas nesta classe
		foreach ($id_subcompetencias_nao_instanciadas as $id_subcompetencia)
		{
			$subcompetencia = new Subcompetencia_model(array(
				'id' => $id_subcompetencia,
				'rubrica' => $this
			));

			$subcompetencia->popular($apenas_estrutura);
			$this->subcompetencias[] = $subcompetencia;
		}
	}

}
