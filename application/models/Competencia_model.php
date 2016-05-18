<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Competencia_model extends CI_Model {
	public $id;
	public $codigo;
	public $nome;

	public $subcompetencias = array();

	private $populando = false;

	public function __construct($param = null)
	{
		if (is_array($param))
		{
			if (isset($param['id']))
			{
				$this->id = $param['id'];
			}
			if (isset($param['codigo']))
			{
				$this->codigo = $param['codigo'];
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

		$this->load->database();
	}

	/**
	 * Popular
	 *
	 * Preenche as propriedades da instância com valores obtidos na base a partir do ID
	 * Retorna esta própria instância para permitir concatenação de funções ou null se não houver ID definido
	 * Se for informado $id_avaliacao, apenas subcompetências da avaliação são incluídas
	 * @return Competencia_model
	 */
	public function popular($apenas_estrutura = false, $id_avaliacao = null)
	{
		if (isset($this->id))
		{
			if (!$this->populando)
			{
				$this->populando = true;

				$dados_instancia = $this->db->get_where('competencias', array('id' => $this->id))->row();

				if (!isset($this->codigo))
				{
					$this->codigo = $dados_instancia->codigo;
				}
				if (!isset($this->nome))
				{
					$this->nome = $dados_instancia->nome;
				}

				if (empty($this->subcompetencias))
				{
					$this->popular_subcompetencias($apenas_estrutura, $id_avaliacao);
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
	 * Preenche a propriedade $subcompetencias com as subcompetências associadas a esta instância
	 * Se for informado $id_avaliacao, apenas subcompetências da avaliação são incluídas
	 */
	public function popular_subcompetencias($apenas_estrutura = false, $id_avaliacao = null)
	{
		$this->load->helper(array(
			'class_helper',
			'obj_array_helper'
		));

		carregar_classe('models/Subcompetencia_model');

		if (isset($id_avaliacao))
		{
			// Buscar apenas subcompetências da avaliação
			$this->db
				->distinct()
				->select('s.id')
				->join('subcompetencias_mdl_gradingform_rubric_criteria sgrc', 'sgrc.id_subcompetencia = s.id')
				->join('v_rubricas_avaliacoes vra', 'vra.id_mdl_gradingform_rubric_criteria = sgrc.id_mdl_gradingform_rubric_criteria')
				->where('vra.id_avaliacao', $id_avaliacao)
			;
		}

		$consulta = $this->db->get_where('subcompetencias s', array('s.id_competencia' => $this->id));

		$id_subcompetencias_db = obj_array_map_id($consulta->result());
		$id_subcompetencias_nao_instanciadas = array_diff($id_subcompetencias_db, obj_array_map_id($this->subcompetencias));

		// Inclui em $this->subcompetencias todas as subcompetências que estão na base mas não instanciadas nesta classe
		foreach ($id_subcompetencias_nao_instanciadas as $id_subcompetencia)
		{
			$subcompetencia = new Subcompetencia_model(array(
				'id' => $id_subcompetencia,
				'competencia' => $this
			));

			$subcompetencia->popular($apenas_estrutura, $id_avaliacao);
			$this->subcompetencias[] = $subcompetencia;
		}

	}

	public function comparar($cmp1, $cmp2)
	{
		return strcmp($cmp1->codigo, $cmp2->codigo);
	}

}
