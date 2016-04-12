<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Geracao_resultados {
	private $CI;

	public function __construct()
	{
		$this->CI =& get_instance();

		$this->CI->load->database();
	}

	/**
	 * Retorna os resultados de uma disciplina em uma turma por estudante, avaliação e subcompetência
	 * @return array
	 */
	public function obter_resultados_disciplina_turma($disciplina_turma)
	{
		$CI = $this->CI;

		$CI->load->library('Consultas_SQL');

		$id_disciplina_turma = $disciplina_turma->id;
		$estudantes = $disciplina_turma->estudantes;
		$avaliacoes = $disciplina_turma->avaliacoes;

		$dados_resultados = $CI->db->query($CI->consultas_sql->resultados_avaliacoes_disciplina_turma(), array($id_disciplina_turma))->result();

		$estudantes_userid =  array_map(function($est) {return $est->mdl_userid;}, $estudantes);
		$avaliacoes_id =  array_map(function($av) {return $av->id;}, $avaliacoes);

		$resultados_avaliacoes = array();
		$resultados_gerais = array();
		$correcoes_nao_estudantes = array();

		foreach ($dados_resultados as $linha)
		{
			$userid = $linha->userid;
			$id_avaliacao = $linha->id_avaliacao;
			$nome_completo = $linha->nome_completo;
			$id_rubrica = $linha->id_mdl_gradingform_rubric_criteria;

			$idx_estudante = array_search($userid, $estudantes_userid);
			$idx_avaliacao = array_search($id_avaliacao, $avaliacoes_id);

			$avaliacao = $avaliacoes[$idx_avaliacao];

			if ($idx_estudante === false)
			{
				if (!isset($correcoes_nao_estudantes[$id_avaliacao]))
				{
					$correcoes_nao_estudantes[$id_avaliacao] = array(
						'avaliacao' => $avaliacao,
						'nomes' => array($linha->nome_completo)
					);
				}
				else
				{
					$correcoes_nao_estudantes[$id_avaliacao]['nomes'][] = $nome_completo;
				}
			}
			else if (!isset($estudante) || $estudante->mdl_userid !== $userid)
			{
				$estudante = $estudantes[$idx_estudante];
			}

			if (isset($estudante))
			{
				$qtd_rubricas_subcompetencias = $avaliacao->obter_qtd_rubricas_subcompetencias();

				$rubricas_id =  array_map(function($rub) {return $rub->mdl_id;}, $avaliacao->rubricas);

				$idx_rubrica = array_search($id_rubrica, $rubricas_id);
				$rubrica = $avaliacao->rubricas[$idx_rubrica];

				if (!isset($resultados_avaliacoes[$userid]))
				{
					$resultados_avaliacoes[$userid] = array();
				}

				foreach ($rubrica->subcompetencias as $subcompetencia)
				{
					if (!isset($resultados_avaliacoes[$userid][$id_avaliacao]))
					{
						$resultados_avaliacoes[$userid][$id_avaliacao] = array();
					}

					$codigo = $subcompetencia->obter_codigo_sem_obrigatoriedade();

					if (isset($resultados_avaliacoes[$userid][$id_avaliacao][$codigo]))
					{
						$resultados_avaliacoes[$userid][$id_avaliacao][$codigo]['qtd_rubricas_demonstradas'] += (int) $linha->demonstrada;
					}
					else
					{
						$resultados_avaliacoes[$userid][$id_avaliacao][$codigo] = array(
							'qtd_rubricas_demonstradas' => (int) $linha->demonstrada,
							'demonstrada' => false
						);
					}

					$resultados_avaliacoes[$userid][$id_avaliacao][$codigo]['demonstrada'] = $resultados_avaliacoes[$userid][$id_avaliacao][$codigo]['qtd_rubricas_demonstradas'] === $qtd_rubricas_subcompetencias[$codigo];
				}
			}
		}

		foreach ($resultados_avaliacoes as $mdl_userid => $avaliacoes_por_id)
		{
			foreach ($avaliacoes_por_id as $id_avaliacao => $subcompetencias_por_codigo)
			{
				if (!isset($resultados_gerais[$mdl_userid]))
				{
					$resultados_gerais[$mdl_userid] = array();
				}

				foreach ($subcompetencias_por_codigo as $subcompetencia_codigo => $resultados_subcompetencia)
				{
					$competencia_codigo = explode('.', $subcompetencia_codigo)[0];

					if (!isset($resultados_gerais[$mdl_userid][$competencia_codigo]))
					{
						$resultados_gerais[$mdl_userid][$competencia_codigo] = array();
					}
				}

			}
		}

		return array(
			'resultados_avaliacoes' => $resultados_avaliacoes,
			'resultados_gerais' => $resultados_gerais,
			'correcoes_nao_estudantes' => $correcoes_nao_estudantes
		);
	}

}
