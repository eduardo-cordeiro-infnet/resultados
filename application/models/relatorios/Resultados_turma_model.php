<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Resultados_turma_model extends CI_Model {

	public function __construct()
	{
		$this->load
			->library('Consultas_SQL')
			->helper('class_helper')
			->database()
		;
	}

	/**
	 * Obter turmas com rubricas
	 *
	 * Retorna todas as turmas que possuem rubricas associadas a subcompetências
	 * @return array
	 */
	public function obter_turmas_com_resultados()
	{
		$consulta = $this->db->query($this->consultas_sql->turmas_com_caminho(false, true, true));

		$turmas = array();

		foreach ($consulta->result() as $linha)
		{
			$turmas[$linha->id] = $linha->turma_com_caminho;
		}

		return $turmas;
	}

	/**
	 * Obter dados de relatório
	 *
	 * Retorna os dados de resultados de competências por avaliação por estudante
	 * @return array
	 */
	public function obter_dados_relatorio($id_turma)
	{
		carregar_classe('models/Turma_model');

		$turma = new Turma_model($id_turma);
		$turma->popular();

		$erros = array();
		if ($turma->avaliacao_final_inexistente)
		{
			$erros[] = 'Não há nenhuma avaliação definida como "Avaliação final", portanto não é possível calcular os resultados finais da disciplina.';
		}
		else if ($turma->avaliacao_final_sem_rubricas)
		{
			$erros[] = 'A avaliação final não possui rubricas, portanto não é possível calcular os resultados finais da disciplina.';
		}

		$info = array();

		foreach ($turma->avaliacoes_sem_rubrica as $avaliacao)
		{
			$info[] = 'A avaliação <strong>' . $avaliacao->nome . '</strong> não possui rubricas e foi desconsiderada neste relatório.';
		}

		foreach ($turma->rubricas_sem_subcompetencias as $avaliacao_rubrica)
		{
			$info[] = 'Na avaliação <strong>' . $avaliacao_rubrica['avaliacao']->nome . '</strong>, a seguinte rubrica não possui subcompetências associadas e foi desconsiderada neste relatório: <em>' . $avaliacao_rubrica['rubrica']->descricao . '</em>';
		}

		foreach ($turma->correcoes_nao_estudantes as $correcao) {
			$msg = 'Na avaliação <strong>' . $correcao['avaliacao']->nome . '</strong>, os seguintes usuários tiveram entregas corrigidas, mas não estão inscritos como estudantes no Moodle:</p><ul>';
			foreach ($correcao['nomes'] as $nome) {
				$msg .= '<li>' . $nome . '</li>';
			}

			$msg .= '</ul>';

			$info[] = $msg;
		}

		return array(
			'turma' => $turma,
			'estudantes' => $turma->estudantes,
			'avaliacoes' => $turma->avaliacoes,
			'resultados_avaliacoes' => $turma->resultados_avaliacoes,
			'resultados_gerais' => $turma->resultados_gerais,
			'mensagem_erro' => implode('<p>', $erros),
			'mensagem_informativa' => implode('<p>', $info)
		);
	}

}
