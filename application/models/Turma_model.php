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

	public $estudantes = array();
	public $avaliacoes = array();
	public $competencias = array();
	public $resultados_avaliacoes = array();
	public $resultados_gerais = array();

	public $avaliacao_final_inexistente;
	public $avaliacao_final_sem_rubricas;
	public $avaliacoes_sem_rubrica = array();
	public $rubricas_sem_subcompetencias = array();
	public $correcoes_nao_estudantes = array();

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
	public function popular_avaliacoes()
	{
		carregar_classe(array(
			'models/Avaliacao_model',
			'models/Rubrica_model',
			'models/Competencia_model',
			'models/Subcompetencia_model'
		));

		$dados_avaliacoes = $this->db->query($this->consultas_sql->avaliacoes_turma(), array($this->id))->result();

		$avaliacoes_sem_rubrica = array();
		$rubricas_sem_subcompetencias = array();

		$avaliacoes = array();
		$avaliacao_final = null;
		$avaliacao_final_sem_rubricas = false;

		foreach ($dados_avaliacoes as $idx=>$linha)
		{
			$avaliacao = null;
			$rubrica = null;
			$competencia = null;
			$subcompetencia = null;

			$idx_avaliacao = array_search($linha->id_avaliacao, array_map(function($av) {return $av->id;}, $avaliacoes));
			if ($idx_avaliacao !== false)
			{
				$avaliacao = $avaliacoes[$idx_avaliacao];
			}
			else
			{
				$avaliacao = new Avaliacao_model(array(
					'id' => $linha->id_avaliacao,
					'nome' => $linha->nome_avaliacao,
					'avaliacao_final' => $linha->avaliacao_final === '1'
				));

				if ($avaliacao->avaliacao_final)
				{
					$avaliacao_final = $avaliacao;
				}

				$avaliacoes[] = $avaliacao;
			}

			$idx_rubrica = array_search($linha->id_mdl_gradingform_rubric_criteria, array_map(function($rub) {return $rub->mdl_id;}, $avaliacao->rubricas));
			if ($idx_rubrica !== false)
			{
				$rubrica = $avaliacao->rubricas[$idx_rubrica];
			}
			else
			{
				if ($linha->id_mdl_gradingform_rubric_criteria)
				{
					$rubrica = new Rubrica_model(array(
						'mdl_id' => $linha->id_mdl_gradingform_rubric_criteria,
						'descricao' => $linha->rubrica,
						'ordem' => $linha->ordem_rubrica
					));

					$avaliacao->rubricas[] = $rubrica;
				}
				else
				{
					$avaliacoes_sem_rubrica[] = $avaliacao;

					if ($avaliacao_final === $avaliacao)
					{
						$avaliacao_final_sem_rubricas = true;
					}
				}
			}

			$subcompetencias = $avaliacao->obter_subcompetencias();
			$idx_subcompetencia = array_search($linha->codigo_subcompetencia, array_map(function($sub) {return $sub->codigo_completo;}, $subcompetencias));
			if ($idx_subcompetencia !== false)
			{
				$subcompetencia = $subcompetencias[$idx_subcompetencia];
			}
			else
			{
				$idx_competencia = array_search($linha->codigo_competencia, array_map(function($cmp) {return $cmp->codigo;}, $avaliacao->competencias));
				if ($idx_competencia !== false)
				{
					$competencia = $avaliacao->competencias[$idx_competencia];
				}
				else
				{
					if ($linha->codigo_competencia)
					{
						$competencia = new Competencia_model(array(
							'codigo' => $linha->codigo_competencia,
							'nome' => $linha->nome_competencia
						));

						$avaliacao->competencias[] = $competencia;
					}
					else
					{
						$rubricas_sem_subcompetencias[] = array(
							'avaliacao' => $avaliacao,
							'rubrica' => $rubrica
						);
					}
				}

				if ($linha->codigo_subcompetencia)
				{
					$subcompetencia = new Subcompetencia_model(array(
						'codigo_completo' => $linha->codigo_subcompetencia,
						'nome' => $linha->nome_subcompetencia,
						'obrigatoria' => ($linha->obrigatoria_subcompetencia == 1)
					));

					$competencia->subcompetencias[] = $subcompetencia;
				}
			}

			if ($linha->codigo_subcompetencia)
			{
				$rubrica->subcompetencias[] = $subcompetencia;
			}
		}

		$this->avaliacoes = $avaliacoes;
		$this->avaliacao_final_inexistente = !isset($avaliacao_final);
		$this->avaliacao_final_sem_rubricas = $avaliacao_final_sem_rubricas;
		$this->avaliacoes_sem_rubrica = $avaliacoes_sem_rubrica;
		$this->rubricas_sem_subcompetencias = $rubricas_sem_subcompetencias;
	}

	/**
	 * Popular competências
	 *
	 * Preenche as competências da instância a partir das avaliações
	 */
	public function popular_competencias()
	{
		carregar_classe('models/Competencia_model');

		foreach ($this->avaliacoes as $avaliacao)
		{
			foreach ($avaliacao->competencias as $competencia_av) {
				$idx_competencia = array_search($competencia_av->codigo, array_map(function($cmp){return $cmp->codigo;}, $this->competencias));

				if ($idx_competencia === false)
				{
					$competencia = new Competencia_model(array(
						'codigo' => $competencia_av->codigo,
						'nome' => $competencia_av->nome
					));

					$this->competencias[] = $competencia;
				}
				else
				{
					$competencia = $this->competencias[$idx_competencia];
				}

				foreach ($competencia_av->subcompetencias as $subcompetencia) {
					if (array_search($subcompetencia->obter_codigo_sem_obrigatoriedade(), array_map(function($scmp){return $scmp->obter_codigo_sem_obrigatoriedade();}, $competencia->subcompetencias)) === false)
					{
						$competencia->subcompetencias[] = $subcompetencia;
					}
				}
			}
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

	public function obter_link_moodle()
	{
		return (isset($this->id_mdl_course)) ? URL_BASE_LMS . '/course/view.php?id=' . $this->id_mdl_course : null;
	}

}
