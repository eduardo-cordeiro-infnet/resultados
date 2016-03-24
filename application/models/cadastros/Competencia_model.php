<?php
class Competencia_model extends Grocery_CRUD_Model {

	/**
	 * Obter disciplinas de turmas
	 *
	 * Retorna uma lista com as disciplinas que estão associadas a turmas
	 * @return array
	 */
	public function obter_disciplinas_turmas()
	{
		$consulta = $this->db->query("
			select dt.id,
				CONCAT(
					CONCAT_WS(' > ', e.sigla, f.nome, t.nome, b.nome, d.nome),
						case
							when d.denominacao_bloco is not null then CONCAT(' (', d.denominacao_bloco, ')')
							else ''
						end
				) disciplina_turma_com_caminho,
				CONCAT(e.sigla, f.nome, t.nome, b.nome) bloco_com_caminho,
				d.denominacao_bloco
			from disciplinas_turmas dt
				join disciplinas d on d.id = dt.id_disciplina
				join blocos b on b.id = d.id_bloco
				join turmas t on t.id = dt.id_turma
				join formacoes f on f.id = t.id_formacao
				join escolas e on e.id = f.id_escola
			order by bloco_com_caminho, denominacao_bloco
			;
		");

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
		$consulta = $this->db->query("
			select cmp.id,
				CONCAT_WS(' - ', cmp.codigo, cmp.nome) nome_com_codigo
			from competencias cmp
				join disciplinas_turmas dt on dt.id = cmp.id_disciplina_turma
			where dt.id = ?;
		", array($id_disciplina_turma));

		$competencias_disciplina = array();

		foreach ($consulta->result() as $linha)
		{
			$competencias_disciplina[$linha->id] = $linha->nome_com_codigo;
		}

		return $competencias_disciplina;
	}
}
