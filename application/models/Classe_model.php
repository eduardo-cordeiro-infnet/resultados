<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Classe_model extends CI_Model {
	public $id;
	public $nome;
	public $programa;
	public $modalidade;
	public $escola;
	public $id_mdl_course_category;
	public $trimestre;
	public $ano;

	public $turmas = array();

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
			if (isset($param['programa']))
			{
				$this->programa = $param['programa'];
			}
			if (isset($param['modalidade']))
			{
				$this->modalidade = $param['modalidade'];
			}
			if (isset($param['escola']))
			{
				$this->escola = $param['escola'];
			}
			if (isset($param['id_mdl_course_category']))
			{
				$this->id_mdl_course_category = $param['id_mdl_course_category'];
			}
			if (isset($param['trimestre']))
			{
				$this->trimestre = $param['trimestre'];
			}
			if (isset($param['ano']))
			{
				$this->ano = $param['ano'];
			}
			if (isset($param['turmas']))
			{
				$this->turmas = $param['turmas'];
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
	 * Se $apenas_estrutura === true, preenche apenas as propriedades que não dependem de dados do Moodle (por exemplo, nas turmas da classe não preenche $estudantes e $resultados)
	 * Retorna esta própria instância para permitir concatenação de funções ou null se não houver ID definido
	 * @return Classe_model
	 */
	public function popular($apenas_estrutura = false)
	{
		if (isset($this->id))
		{
			if (!$this->populando)
			{
				$this->populando = true;

				$this->load
					->library('Consultas_SQL')
					->helper('class_helper')
				;
				$dados = $this->db->query($this->consultas_sql->dados_classe(), array($this->id))->row();

				if (!isset($this->programa))
				{
					carregar_classe('models/Programa_model');
					$this->programa = new Programa_model(array(
						'id' => $dados->id_programa,
						'nome' => $dados->nome_programa,
						'sigla' => $dados->sigla_programa
					));
				}

				if (!isset($this->modalidade))
				{
					carregar_classe('models/Modalidade_model');
					$this->modalidade = new Modalidade_model(array(
						'id' => $dados->id_modalidade,
						'nome' => $dados->nome_modalidade
					));
				}

				if (!isset($this->escola))
				{
					carregar_classe('models/Escola_model');
					$this->escola = new Escola_model(array(
						'id' => $dados->id_escola,
						'nome' => $dados->nome_escola,
						'sigla' => $dados->sigla_escola
					));
				}

				if (!isset($nome))
				{
					$this->nome = $dados->nome;
				}
				if (!isset($id_mdl_course_category))
				{
					$this->id_mdl_course_category = $dados->id_mdl_course_category;
				}
				if (!isset($trimestre))
				{
					$this->trimestre = $dados->trimestre;
				}
				if (!isset($ano))
				{
					$this->ano = $dados->ano;
				}
				if (empty($this->turmas))
				{
					$this->popular_turmas($apenas_estrutura);
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

	public function popular_turmas($apenas_estrutura = false)
	{
		$this->load->helper('obj_array');
		carregar_classe('models/Turma_model');

		// Lista de IDs de turmas da classe obtidas da base de dados
		$id_turmas_db = obj_array_map_id($this->db->get_where('turmas', array('id_classe' => $this->id))->result());
		$id_turmas_nao_instanciadas = array_diff($id_turmas_db, obj_array_map_id($this->turmas));

		// Inclui em $this->turmas todas as turmas que estão na base mas não instanciadas nesta classe
		foreach ($id_turmas_nao_instanciadas as $id_turma)
		{
			$turma = new Turma_model(array(
				'id' => $id_turma,
				'classe' => $this
			));

			$this->turmas[] = $turma;
			$turma->popular($apenas_estrutura);
		}
	}

}
