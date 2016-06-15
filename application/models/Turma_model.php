<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Turma_model extends CI_Model {
	public $id;
	public $disciplina;
	public $classe;
	public $trimestre_inicio;
	public $ano_inicio;
	public $trimestre_fim;
	public $ano_fim;
	public $id_mdl_course;

	public $avaliacoes = array();
	public $competencias = array();
	public $rubricas = array();

	public $estudantes = array();
	public $resultados_avaliacoes = array();
	public $resultados_gerais = array();

	public $avaliacao_final_inexistente;
	public $avaliacao_final_sem_rubricas;
	public $avaliacoes_sem_rubrica = array();
	public $rubricas_sem_subcompetencias = array();
	public $correcoes_nao_estudantes = array();

	private $populando = false;

	public function __construct($param = null)
	{
		if (is_array($param))
		{
			if (isset($param['id']))
			{
				$this->id = $param['id'];
			}
			if (isset($param['disciplina']))
			{
				$this->disciplina = $param['disciplina'];
			}
			if (isset($param['classe']))
			{
				$this->classe = $param['classe'];
			}
			if (isset($param['trimestre_inicio']))
			{
				$this->trimestre_inicio = $param['trimestre_inicio'];
			}
			if (isset($param['ano_inicio']))
			{
				$this->ano_inicio = $param['ano_inicio'];
			}
			if (isset($param['trimestre_fim']))
			{
				$this->trimestre_fim = $param['trimestre_fim'];
			}
			if (isset($param['ano_fim']))
			{
				$this->ano_fim = $param['ano_fim'];
			}
			if (isset($param['id_mdl_course']))
			{
				$this->id_mdl_course = $param['id_mdl_course'];
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
	 * Se $apenas_estrutura === true, preenche apenas as propriedades que não dependem de dados do Moodle (não preenche $estudantes e $resultados)
	 * Retorna esta própria instância para permitir concatenação de funções ou null se não houver ID definido
	 * @return Turma_model
	 */
	public function popular($apenas_estrutura = false)
	{
		if (isset($this->id))
		{
			if (!$this->populando)
			{
				$this->populando = true;

				$this->load->helper('class_helper');

				$dados_instancia = $this->db->where('id', $this->id)->get('turmas')->row();

				if (!isset($this->trimestre_inicio))
				{
					$this->trimestre_inicio = $dados_instancia->trimestre_inicio;
				}
				if (!isset($this->ano_inicio))
				{
					$this->ano_inicio = $dados_instancia->ano_inicio;
				}
				if (!isset($this->trimestre_fim))
				{
					$this->trimestre_fim = $dados_instancia->trimestre_fim;
				}
				if (!isset($this->ano_fim))
				{
					$this->ano_fim = $dados_instancia->ano_fim;
				}
				if (!isset($this->id_mdl_course))
				{
					$this->id_mdl_course = $dados_instancia->id_mdl_course;
				}

				if (!isset($this->disciplina))
				{
					carregar_classe('models/Disciplina_model');
					$this->disciplina = new Disciplina_model(array('id' => $dados_instancia->id_disciplina));
				}
				$this->disciplina->popular($apenas_estrutura);

				if (!isset($this->classe))
				{
					carregar_classe('models/Classe_model');
					$this->classe = new Classe_model(array('id' => $dados_instancia->id_classe));
				}
				$this->classe->popular($apenas_estrutura);

				if (empty($this->avaliacoes))
				{
					$this->popular_avaliacoes();
				}
				if (empty($this->competencias))
				{
					$this->popular_competencias();
				}
				if ($apenas_estrutura !== true)
				{
					if (empty($this->estudantes))
					{
						$this->popular_estudantes();
					}
					if (empty($this->resultados))
					{
						$this->popular_resultados();
					}
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
	 * Popular estudantes
	 *
	 * Preenche a propriedade `estudantes` com todos os usuários inscritos no Moodle na turma
	 */
	public function popular_estudantes()
	{
		carregar_classe('models/Estudante_model');

		$this->estudantes = $this->db->query($this->consultas_sql->estudantes_turma(), array($this->id))->result('Estudante_model');
	}

	/**
	 * Popular avaliações
	 *
	 * Preenche as propriedades da instância referentes a avaliações cadastradas na turma,
	 * inclusive rubricas e subcompetências associadas
	 */
	public function popular_avaliacoes($apenas_estrutura = false)
	{
		$this->load->helper('obj_array');
		carregar_classe('models/Avaliacao_model');

		// Lista de IDs de turmas da classe obtidas da base de dados
		$id_avaliacoes_db = obj_array_map_id($this->db->get_where('avaliacoes', array('id_turma' => $this->id))->result());
		$id_avaliacoes_nao_instanciadas = array_diff($id_avaliacoes_db, obj_array_map_id($this->avaliacoes));

		// Inclui em $this->turmas todas as turmas que estão na base mas não instanciadas nesta classe
		foreach ($id_avaliacoes_nao_instanciadas as $id_avaliacao)
		{
			$avaliacao = new Avaliacao_model(array(
				'id' => $id_avaliacao,
				'turma' => $this
			));

			$this->avaliacoes[] = $avaliacao;
			$avaliacao->popular($apenas_estrutura);
		}

		$avaliacoes_sem_rubrica = array();
		$rubricas_sem_subcompetencias = array();

		foreach ($this->avaliacoes as $avaliacao)
		{
			if (!isset($avaliacao_final) && $avaliacao->avaliacao_final)
			{
				$avaliacao_final = $avaliacao;
			}

			if (empty($avaliacao->rubricas))
			{
				$avaliacoes_sem_rubrica[] = $avaliacao;
			}

			foreach ($avaliacao->rubricas as $rubrica)
			{
				if (empty($rubrica->subcompetencias))
				{
					$rubricas_sem_subcompetencias[] = array(
						'avaliacao' => $avaliacao,
						'rubrica' => $rubrica
					);
				}
			}
		}

		$this->avaliacao_final_inexistente = !isset($avaliacao_final);
		$this->avaliacao_final_sem_rubricas = empty($avaliacao_final->rubricas);
		$this->avaliacoes_sem_rubrica = $avaliacoes_sem_rubrica;
		$this->rubricas_sem_subcompetencias = $rubricas_sem_subcompetencias;
	}

	/**
	 * Popular competências
	 *
	 * Preenche as competências da instância a partir das avaliações
	 */
	public function popular_competencias($apenas_estrutura = false)
	{
		$this->load->helper('obj_array');
		carregar_classe('models/Competencia_model');

		// Lista de IDs de competências da turma obtidas da base de dados
		$id_competencias_db = obj_array_map_id($this->db->get_where('competencias', array('id_turma' => $this->id))->result());
		$id_competencias_nao_instanciadas = array_diff($id_competencias_db, obj_array_map_id($this->competencias));

		// Inclui em $this->competencias todas as competências que estão na base mas não instanciadas nesta turma
		foreach ($id_competencias_nao_instanciadas as $id_competencia)
		{
			$competencia = new Competencia_model(array(
				'id' => $id_competencia,
				'turma' => $this
			));
			$competencia->popular($apenas_estrutura);
			$this->competencias[] = $competencia;
		}

		if (count($this->competencias) > 0)
		{
			usort($this->competencias, array($this->competencias[0], 'comparar'));

			foreach ($this->competencias as $competencia) {
				if (count($competencia->subcompetencias) > 0)
				{
					usort($competencia->subcompetencias, array($competencia->subcompetencias[0], 'comparar'));
				}
			}
		}
	}

	/**
	 * Popular resultados
	 *
	 * Preenche a propriedade `resultados` com
	 * resultados por avaliação, subcompetência e rubrica
	 */
	public function popular_resultados()
	{
		$this->load->library('Geracao_resultados');

		$resultados = $this->geracao_resultados->obter_resultados_turma($this);

		$this->resultados_avaliacoes = $resultados['resultados_avaliacoes'];
		$this->resultados_gerais = $resultados['resultados_gerais'];
		$this->correcoes_nao_estudantes = $resultados['correcoes_nao_estudantes'];
	}

	/**
	 * Obter período
	 *
	 * Retorna o período da disciplina formatado
	 * @return string
	 */
	public function obter_periodo() {
		$periodo_inicio = implode('T', array_filter(array($this->trimestre_inicio, $this->ano_inicio)));
		$periodo_fim = implode('T', array_filter(array($this->trimestre_fim, $this->ano_fim)));

		if ($periodo_inicio && $periodo_fim) {
			$periodo = $periodo_inicio . ' a ' . $periodo_fim;
		} else {
			$periodo = $periodo_inicio ?: $periodo_fim;
		}

		return $periodo;
	}

	/**
	 * Obter subcompetências
	 *
	 * Retorna todas as subcompetências da turma
	 * @return array
	 */
	public function obter_subcompetencias()
	{
		$subcompetencias = array();

		foreach ($this->competencias as $competencia)
		{
			$subcompetencias = array_merge($subcompetencias, $competencia->subcompetencias);
		}

		return $subcompetencias;
	}

	/**
	 * Obter quantidades de avaliações por subcompetências
	 *
	 * Retorna a quantidade de avaliações em que cada subcompetência é verificada,
	 * desconsiderando a avaliação final
	 * @return array
	 */
	public function obter_qtd_avaliacoes_subcompetencias()
	{
		$qtd_avaliacoes_subcompetencia = array();

		foreach ($this->obter_subcompetencias() as $subcompetencia)
		{
			$codigo_subcompetencia = $subcompetencia->obter_codigo_sem_obrigatoriedade();

			$qtd_avaliacoes_subcompetencia[$codigo_subcompetencia] = count(
				array_filter($this->avaliacoes,
					function($av) use ($codigo_subcompetencia) {
						return $av->avaliacao_final === false
							&& array_search($codigo_subcompetencia,
								array_map(
									function($scmp) {
										return $scmp->obter_codigo_sem_obrigatoriedade();
									}, $av->obter_subcompetencias()
								)
							) !== false;
					}
				)
			);
		}

		return $qtd_avaliacoes_subcompetencia;
	}

	/**
	 * Obter avaliação final
	 *
	 * Retorna a avaliação final da disciplina, se houver
	 * @return Avaliacao_model
	 */
	public function obter_avaliacao_final()
	{
		$avaliacao = null;

		if (!$this->avaliacao_final_inexistente)
		{
			foreach ($this->avaliacoes as $av)
			{
				if ($av->avaliacao_final)
				{
					$avaliacao = $av;
					break;
				}
			}
		}

		return $avaliacao;
	}

	/**
	 * Obter link Moodle
	 *
	 * Retorna a URL do curso Moodle associado à turma
	 * @return Avaliacao_model
	 */
	public function obter_link_moodle()
	{
		return (isset($this->id_mdl_course)) ? URL_BASE_LMS . '/course/view.php?id=' . $this->id_mdl_course : null;
	}

	/**
	 * Obter rubricas
	 *
	 * Retorna as rubricas das avaliações da turma
	 * @return array
	 */
	public function obter_rubricas()
	{
		$this->load->helper('obj_array');
		$rubricas_avaliacoes = obj_array_map_prop($this->avaliacoes, 'rubricas');

		return call_user_func_array('array_merge', $rubricas_avaliacoes);
	}

	public function __toString()
	{
		$nome_classe = (isset($this->classe)) ? (string) $this->classe : null;
		$nome_disciplina = (isset($this->disciplina)) ? (string) $this->disciplina : null;
		return implode(' > ', array($nome_classe, $nome_disciplina));
	}

}
