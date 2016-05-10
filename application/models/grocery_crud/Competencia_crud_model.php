<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Competencia_crud_model extends Grocery_CRUD_Model {

	/**
	 * Obter turmas de classes
	 *
	 * Retorna uma lista com as turmas de uma classe específica
	 * @return array
	 */
	public function obter_turmas($id_turma)
	{
		$this->load->library('Consultas_SQL');
		$consulta = $this->db->query($this->consultas_sql->turmas_com_caminho(), array($id_turma));

		$turmas = array();

		foreach ($consulta->result() as $linha) {
			$turmas[$linha->id] = $linha->turma_com_caminho;
		}

		return $turmas;
	}

	/**
	 * Obter coluna turma
	 *
	 * Retorna o caminho da turma da linha, formatado para destacar o nome da disciplina
	 * @return string
	 */
	public function obter_coluna_turma($valor, $linha) {
		$turmas = $this->obter_turmas($valor);
		foreach ($turmas as $id_turma => $turma_com_caminho_loop) {
			if ($id_turma == $valor)
			{
				$turma_com_caminho = $turma_com_caminho_loop;
				break;
			}
		}

		$this->load->helper('format');
		return formatar_caminho($turma_com_caminho);
	}

	/**
	 * Obter caminho do cadastro de subcompetências
	 *
	 * Retorna o caminho do cadastro de subcompetências com o id_turma
	 */
	public function obter_caminho_subcompetencias($valor, $linha)
	{
		return site_url('cadastros/competencia/subcompetencias/' . $this->db->select('id_turma')->get_where('competencias', 'id = '.$valor)->result()[0]->id_turma);
	}

	/**
	 * Obter competências de turma
	 *
	 * Retorna as competências de uma turma
	 * @return array
	 */
	public function obter_competencias_turma($id_turma)
	{
		$this->load->library('Consultas_SQL');
		$consulta = $this->db->query($this->consultas_sql->competencias_turma(), array($id_turma));

		$competencias_disciplina = array();

		foreach ($consulta->result() as $linha)
		{
			$competencias_disciplina[$linha->id] = $linha->nome_com_codigo;
		}

		return $competencias_disciplina;
	}
}
