<?php
class Competencia_model extends Grocery_CRUD_Model {

	/**
	 * Obter disciplinas de turmas
	 *
	 * Retorna uma lista com as disciplinas que estão associadas a turmas
	 * @return array
	 */
	public function obter_disciplinas_turmas($id_disciplina_turma)
	{
		$this->load->library('Consultas_SQL');
		$consulta = $this->db->query($this->consultas_sql->disciplinas_turmas_com_caminho(), array($id_disciplina_turma));

		$disciplinas_turmas = array();

		foreach ($consulta->result() as $linha) {
			$disciplinas_turmas[$linha->id] = $linha->disciplina_turma_com_caminho;
		}

		return $disciplinas_turmas;
	}

	/**
	 * Obter código de subcompetência
	 *
	 * Retorna o código da subcompetência prefixado com o código da competência
	 */
	public function obter_codigo_subcompetencia($valor, $linha)
	{
		return $linha->codigo_competencia_red . "." . $linha->codigo;
	}
	/**
	 * Obter caminho do cadastro de subcompetências
	 *
	 * Retorna o caminho do cadastro de subcompetências com o id_disciplina_turma
	 */
	public function obter_caminho_subcompetencias($valor, $linha)
	{
		return site_url('cadastros/competencia/subcompetencias/' . $this->db->select('id_disciplina_turma')->get_where('competencias', 'id = '.$valor)->result()[0]->id_disciplina_turma);
	}

	/**
	 * Obter competências de disciplina
	 *
	 * Retorna uma lista com as competências que estão associadas à disciplina da turma
	 * @return array
	 */
	public function obter_competencias_disciplina($id_disciplina_turma)
	{
		$this->load->library('Consultas_SQL');
		$consulta = $this->db->query($this->consultas_sql->competencias_disciplina_turma(), array($id_disciplina_turma));

		$competencias_disciplina = array();

		foreach ($consulta->result() as $linha)
		{
			$competencias_disciplina[$linha->id] = $linha->nome_com_codigo;
		}

		return $competencias_disciplina;
	}
}
