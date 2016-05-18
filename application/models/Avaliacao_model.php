<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Avaliacao_model extends CI_Model {
	public $id;
	public $turma;
	public $nome;
	public $avaliacao_final;

	public $rubricas = array();
	public $competencias = array();
	public $ids_mdl_course_modules = array();

	private $populando = false;

	public function __construct($param = null)
	{
		if (is_array($param))
		{
			if (isset($param['id']))
			{
				$this->id = $param['id'];
			}
			if (isset($param['turma']))
			{
				$this->turma = $param['turma'];
			}
			if (isset($param['nome']))
			{
				$this->nome = $param['nome'];
			}
			if (isset($param['avaliacao_final']))
			{
				$this->avaliacao_final = $param['avaliacao_final'];
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
	 * @return Avaliacao_model
	 */
	public function popular($apenas_estrutura = false)
	{
		if (isset($this->id))
		{
			if (!$this->populando)
			{
				$this->populando = true;

				$this->load->helper('class_helper');

				$dados_instancia = $this->db->get_where('avaliacoes', array('id' => $this->id))->row();

				if (!isset($this->nome))
				{
					$this->nome = $dados_instancia->nome;
				}
				if (!isset($this->avaliacao_final))
				{
					$this->avaliacao_final = $dados_instancia->avaliacao_final;
				}

				if (!isset($this->turma))
				{
					carregar_classe('models/Turma_model');
					$this->turma = new Turma_model(array('id' => $dados_instancia->id_turma));
				}
				$this->turma->popular(true);

				if (empty($this->rubricas))
				{
					$this->popular_rubricas($apenas_estrutura);
				}
				if (empty($this->competencias))
				{
					$this->popular_competencias($apenas_estrutura);
				}
				if (empty($this->ids_mdl_course_modules))
				{
					$this->popular_ids_mdl_course_modules($apenas_estrutura);
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
	 * Popular rubricas
	 *
	 * Preenche a propriedade $rubricas com as rubricas cadastradas na tarefa do Moodle
	 */
	public function popular_rubricas($apenas_estrutura = false)
	{
		$this->load->helper('obj_array_helper');

		carregar_classe('models/Rubrica_model');

		$id_rubricas_db = obj_array_map_prop(
			$this->db->get_where('v_rubricas_avaliacoes', array('id_avaliacao' => $this->id))->result(),
			'id_mdl_gradingform_rubric_criteria'
		);
		$id_rubricas_nao_instanciadas = array_diff($id_rubricas_db, obj_array_map_prop($this->rubricas, 'mdl_id'));

		// Inclui em $this->rubricas todas as rubricas que estão na base mas não instanciadas nesta classe
		foreach ($id_rubricas_nao_instanciadas as $id_mdl_gradingform_rubric_criteria)
		{
			$rubrica = new Rubrica_model(array(
				'mdl_id' => $id_mdl_gradingform_rubric_criteria,
				'avaliacao' => $this
			));

			$this->rubricas[] = $rubrica;
			$rubrica->popular($apenas_estrutura);
		}
	}

	/**
	 * Popular competências
	 *
	 * Preenche a propriedade $competencias a partir das subcompetências das rubricas da instância
	 */
	public function popular_competencias($apenas_estrutura = false)
	{
		$this->load->helper('obj_array_helper');

		carregar_classe('models/Competencia_model');

		$id_competencias_db = obj_array_map_prop(
			$this->db
				->distinct()
				->select('id_competencia')
				->where_in(
					'id',
					obj_array_map_id(array($this, 'obter_subcompetencias'))
				)
				->get('subcompetencias')
				->result(),
			'id_competencia'
		);
		$id_competencias_nao_instanciadas = array_diff($id_competencias_db, obj_array_map_id($this->competencias));

		// Inclui em $this->competencias todas as competências que estão
		// associadas às rubricas desta avaliação na base mas não instanciadas nesta classe
		foreach ($id_competencias_nao_instanciadas as $id_competencia)
		{
			$competencia = new Competencia_model($id_competencia);

			$this->competencias[] = $competencia;
			// Popular a competência fornecendo o ID da avaliação,
			// para buscar apenas subcompetências associadas às rubricas da avaliação
			$competencia->popular($apenas_estrutura, $this->id);
		}
	}

	/**
	 * Popular IDs de mdl_course_modules
	 *
	 * Preenche a propriedade $ids_mdl_course_modules a partir dos módulos do Moodle associados à avaliação
	 */
	public function popular_ids_mdl_course_modules($apenas_estrutura = false)
	{
		$instances_mdl_course_modules_db = obj_array_map_prop(
			$this->db->get_where('avaliacoes_mdl_course_modules', array('id_avaliacao' => $this->id))->result(),
			'instance_mdl_course_modules'
		);

		$ids_mdl_course_modules_db = obj_array_map_id(
			$this->db->query(
				$this->consultas_sql->mdl_modulo_com_caminho_mdl_categoria(null, count($instances_mdl_course_modules_db)),
				array($instances_mdl_course_modules_db)
			)->result()
		);

		// Inclui em $this->instances_mdl_course_modules_db todas as instâncias de módulos Moodle que estão
		// associadas a esta avaliação na base mas não incluídas nesta classe
		$this->ids_mdl_course_modules = array_merge($ids_mdl_course_modules_db, $this->ids_mdl_course_modules);
	}

	/**
	 * Obter subcompetências
	 *
	 * Retorna todas as subcompetências das rubricas da avaliação
	 * @return array
	 */
	public function obter_subcompetencias()
	{
		$subcompetencias = array();

		if (!empty($this->competencias))
		{
			foreach ($this->competencias as $competencia)
			{
				$subcompetencias = array_merge_recursive($subcompetencias, $competencia->subcompetencias);
			}
		}
		else if (!empty($this->rubricas))
		{
			foreach ($this->rubricas as $rubrica)
			{
				foreach ($rubrica->subcompetencias as $subcompetencia) {
					if (!in_array($subcompetencia->id, obj_array_map_id($subcompetencias)))
					{
						$subcompetencias[] = $subcompetencia;
					}
				}
			}
		}

		return $subcompetencias;
	}

	/**
	 * Obter quantidade de rubricas por subcompetência
	 *
	 * Retorna todas as subcompetências da avaliação
	 * e a quantidade de rubricas associadas a cada uma
	 * @return array
	 */
	public function obter_qtd_rubricas_subcompetencias()
	{
		$qtd_rubricas_subcompetencias = array();

		foreach ($this->rubricas as $rubrica)
		{
			foreach ($rubrica->subcompetencias as $subcompetencia)
			{
				$codigo = $subcompetencia->obter_codigo_sem_obrigatoriedade();

				if (isset($qtd_rubricas_subcompetencias[$codigo]))
				{
					$qtd_rubricas_subcompetencias[$codigo]++;
				}
				else
				{
					$qtd_rubricas_subcompetencias[$codigo] = 1;
				}
			}
		}

		return $qtd_rubricas_subcompetencias;
	}

	/**
	 * Obter links de avaliações no Moodle
	 *
	 * Retorna HTML de links para as avaliações da turma no LMS com o ícone do Moodle
	 * @return string
	 */
	public function obter_links_moodle($id_avaliacao = null)
	{
		if ($id_avaliacao === null)
		{
			$id_avaliacao = $this->id;
		}

		$this->load->library('Consultas_SQL');
		$consulta = $this->db->query($this->consultas_sql->mdl_modulos_avaliacoes(), array($id_avaliacao));

		$links = '';

		foreach ($consulta->result() as $lin)
		{
			$links .= anchor_popup(
				URL_BASE_LMS . '/mod/assign/view.php?id=' . $lin->id,
				img(
					array(
						'src' => base_url('assets/img/moodle-m-65x46.png'),
						'alt' => $lin->name,
						'title' => $lin->name,
						'class' => 'tamanho-icone'
					)
				)
			);
		}

		return $links;
	}

	/**
	 * Obter caminhos de módulos do Moodle
	 *
	 * Retorna lista com o código de instância e caminho de categoria e curso dos módulos Moodle associados à avaliação
	 * @return array
	 */
	public function obter_caminhos_modulos_moodle()
	{
		return $this->db->query(
			$CI->consultas_sql->mdl_modulo_com_caminho_mdl_categoria(null, count($avaliacao->ids_mdl_course_modules)),
			array($avaliacao->ids_mdl_course_modules)
		)->result();
	}

	/**
	 * Obter links Moodle
	 *
	 * Retorna lista com URL de acesso aos módulos Moodle associados à avaliação
	 * ou apenas dos que forem informados em $ids_mdl_course_modules
	 * @return array
	 */
	public function obter_links_moodle_sem_icone($ids_mdl_course_modules = null)
	{
		$retorno = array();

		if (!isset($ids_mdl_course_modules))
		{
			$ids_mdl_course_modules = $this->ids_mdl_course_modules;
		}
		else if (!is_array($ids_mdl_course_modules))
		{
			$ids_mdl_course_modules = array($ids_mdl_course_modules);
		}

		foreach ($ids_mdl_course_modules as $instance)
		{
			$retorno[] = URL_BASE_LMS . '/mod/assign/view.php?id=' . $instance;
		}

		return $retorno;
	}

}
