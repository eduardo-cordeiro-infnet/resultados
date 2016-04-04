<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Resultados_turma_model extends CI_Model {
	public function __construct()
	{
		$this->load->database();
		$this->load
			->library('Consultas_SQL')
			->helper('class_helper');
	}

	/**
	 * Obter disciplinas de turmas com rubricas
	 *
	 * Retorna todas as disciplinas de turmas que possuem rubricas associadas a subcompetências
	 * @return array
	 */
	public function obter_disciplinas_turmas_com_resultados()
	{
		$consulta = $this->db->query($this->consultas_sql->disciplinas_turmas_com_caminho(false, true, true));

		$disciplinas_turmas = array();

		foreach ($consulta->result() as $linha)
		{
			$disciplinas_turmas[$linha->id] = $linha->disciplina_turma_com_caminho;
		}

		return $disciplinas_turmas;
	}

	/**
	 * Obter dados de relatório
	 *
	 * Retorna os dados de resultados de competências por avaliação por estudante
	 * @return array
	 */
	public function obter_dados_relatorio($id_disciplina_turma)
	{
		$dados_relatorio = $this->db->query($this->consultas_sql->resultados_avaliacoes_disciplina_turma(), array($id_disciplina_turma))->result();

		//$avaliacoes = $this->obter_avaliacoes_disciplina($dados_relatorio);

		$dados['estudantes'] = $this->obter_estudantes_turma($dados_relatorio);
		return $dados;
	}

	/**
	 * Obter estudantes de turma
	 *
	 * Retorna todos os estudantes inscritos no Moodle em uma disciplina de uma turma
	 * @return array
	 */
	private function obter_estudantes_turma($dados_relatorio)
	{
		carregar_classe('models/Estudante_model');

		$estudantes = array();

		foreach ($dados_relatorio as $linha)
		{
			$item_existente = false;

			foreach ($estudantes as $estudante)
			{
				if ($linha->mdl_userid == $estudante->mdl_userid)
				{
					$item_existente = true;
					break;
				}
			}

			if (!$item_existente)
			{
				$estudantes[] = new Estudante_model(array(
					'nome_completo' => $linha->nome_completo,
					'email' => $linha->email,
					'mdl_username' => $linha->mdl_username,
					'mdl_userid' => $linha->mdl_userid
				));
			}
		}

		return $estudantes;
	}


	/**
	 * Obter avaliações de disciplina
	 *
	 * Retorna todas as avaliações ativas cadastradas em uma disciplina de uma turma,
	 * inclusive rubricas e subcompetências associadas
	 * @return array
	 */
	private function obter_avaliacoes_disciplina($id_disciplina_turma)
	{
		carregar_classe(array(
			'models/Avaliacao_model',
			'models/Rubrica_model',
			'models/Subcompetencia_model'
		));

		$avaliacoes = array();

		$consulta = $this->db->query($this->consultas_sql->avaliacoes_disciplina_turma(), array($id_disciplina_turma));

		foreach ($consulta->result() as $linha) {

		}

		return $avaliacoes;
	}
}
