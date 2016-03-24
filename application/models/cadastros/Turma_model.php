<?php
class Turma_model extends Grocery_CRUD_Model {

	/**
	 * Obter link Moodle
	 *
	 * Retorna HTML de um link para a categoria da turma no LMS com o ícone do Moodle
	 * @return string
	 */
	public function obter_link_moodle($valor, $linha)
	{
		if ($linha->id_mdl_course_category)
		{
			return anchor_popup(
				URL_BASE_LMS . '/course/index.php?categoryid=' . $linha->id_mdl_course_category,
				img(
					array(
						'src' => base_url('assets/img/moodle-m-65x46.png'),
						'alt' => 'Acessar turma no Moodle',
						'title' => 'Acessar turma no Moodle',
						'class' => 'tamanho-icone'
					)
				)
			);
		}
	}

	/**
	 * Obter link da disciplina Moodle
	 *
	 * Retorna HTML de um link para a disciplina no LMS com o ícone do Moodle
	 * @return string
	 */
	public function obter_link_disciplina_moodle($valor, $linha)
	{
		if ($linha->id_mdl_course)
		{
			return anchor_popup(
				URL_BASE_LMS . '/course/view.php?id=' . $linha->id_mdl_course,
				img(
					array(
						'src' => base_url('assets/img/moodle-m-65x46.png'),
						'alt' => 'Acessar disciplina no Moodle',
						'title' => 'Acessar disciplina no Moodle',
						'class' => 'tamanho-icone'
					)
				)
			);
		}
	}

	/**
	 * Obter nome com link Moodle
	 *
	 * Retorna o nome da turma com um link para a categoria da turma no LMS, se houver
	 * @return string
	 */
	public function obter_nome_com_link_moodle($valor, $linha)
	{
		$retorno = $linha->nome;

		if ($linha->id_mdl_course_category) {
			$retorno .= $this->obter_link_moodle($valor, $linha);
		}

		return $retorno;
	}

	/**
	 * Obter categorias Moodle
	 *
	 * Retorna o caminho completo de todas as categorias do Moodle, ordenadas por hierarquia
	 * @return array
	 */
	public function obter_categorias_moodle()
	{
		$consulta = $this->db->query("
			select c.id,
				CONCAT_WS(' > ', c6.name, c5.name, c4.name, c3.name, c2.name, c.name) categoria_com_caminho
			from lmsinfne_mdl.mdl_course_categories c
			  left join lmsinfne_mdl.mdl_course_categories c2 on c2.id = c.parent
			  left join lmsinfne_mdl.mdl_course_categories c3 on c3.id = c2.parent
			  left join lmsinfne_mdl.mdl_course_categories c4 on c4.id = c3.parent
			  left join lmsinfne_mdl.mdl_course_categories c5 on c5.id = c4.parent
			  left join lmsinfne_mdl.mdl_course_categories c6 on c6.id = c5.parent
			order by c.path;
		");

		$categorias_moodle = array();

		foreach ($consulta->result() as $linha) {
			$categorias_moodle[$linha->id] = $linha->categoria_com_caminho;
		}

		return $categorias_moodle;
	}

	/**
	 * Obter cursos Moodle
	 *
	 * Retorna o caminho completo de todos os cursos do Moodle, ordenados por hierarquia
	 * Se houver turma do moodle associada à turma, os cursos dessa turma são ordenados no início
	 * @return array
	 */
	public function obter_cursos_moodle($id_turma = 0)
	{
		$consulta = $this->db->query("
			select crs.id,
				CONCAT_WS(' > ', c6.name, c5.name, c4.name, c3.name, c2.name, c.name, crs.fullname) curso_com_caminho,
				case when ? in (select id from turmas where id_mdl_course_category in (c6.id, c5.id, c4.id, c3.id, c2.id, c.id)) then 1 else 0 end disciplina_turma
			from lmsinfne_mdl.mdl_course crs
				left join lmsinfne_mdl.mdl_course_categories c on c.id = crs.category
				left join lmsinfne_mdl.mdl_course_categories c2 on c2.id = c.parent
				left join lmsinfne_mdl.mdl_course_categories c3 on c3.id = c2.parent
				left join lmsinfne_mdl.mdl_course_categories c4 on c4.id = c3.parent
				left join lmsinfne_mdl.mdl_course_categories c5 on c5.id = c4.parent
				left join lmsinfne_mdl.mdl_course_categories c6 on c6.id = c5.parent
			order by disciplina_turma desc, c.path;
		", array($id_turma));

		$cursos_moodle = array();

		foreach ($consulta->result() as $linha) {
			$cursos_moodle[$linha->id] = $linha->curso_com_caminho;
		}

		return $cursos_moodle;
	}

	/**
	 * Obter disciplinas com blocos
	 *
	 * Retorna todas as disciplinas cadastradas, com o nome do bloco associado, se houver
	 * No estado "add", não retorna disciplinas já associadas, para impedir duplicidade
	 * Disciplinas de blocos que estejam associados à turma são exibidas nas primeiras opções
	 * @return array
	 */
	public function obter_disciplinas_blocos($id_turma = 0, $state = null)
	{
		$sql = "
			select d.id,
				CONCAT(d.nome, case when b.id is not null then CONCAT(' (', b.nome, ')') end) disciplina_com_caminho,
				case when b.id
					in (select id_bloco_red from disciplinas_turmas where id_turma = ?)
				then 1 else 0 end bloco_turma
			from disciplinas d
				left join blocos b on b.id = d.id_bloco
				left join disciplinas_turmas dt on dt.id_turma = ?
					and dt.id_disciplina = d.id
		";

		if ($state == 'add')
		{
			$sql .= "where dt.id is null";
		}

		$sql .= "
			order by bloco_turma desc, b.id, d.nome;
		";

		$consulta = $this->db->query($sql, array($id_turma, $id_turma));

		$disciplinas_blocos = array();

		foreach ($consulta->result() as $linha) {
			$disciplinas_blocos[$linha->id] = $linha->disciplina_com_caminho;
		}

		return $disciplinas_blocos;
	}

	/**
	 * Obter campo disciplina
	 *
	 * Retorna um campo de seleção com a lista de disciplinas
	 */
	public function obter_campo_disciplina($valor)
	{
		$this->load->helper('form');

		return form_dropdown('id_disciplina', $this->obter_disciplinas_blocos($this->uri->segment(4)), $valor);
	}

	/**
	 * Obter período da disciplina
	 *
	 * Retorna o nome da turma com um link para a categoria da turma no LMS, se houver
	 * @return string
	 */
	public function obter_periodo_disciplina($valor, $linha) {
		$periodo_inicio = implode('T', array_filter(array($linha->trimestre_inicio, $linha->ano_inicio)));
		$periodo_fim = implode('T', array_filter(array($linha->trimestre_fim, $linha->ano_fim)));

		if ($periodo_inicio && $periodo_fim) {
			$periodo = $periodo_inicio . ' a ' . $periodo_fim;
		} else {
			$periodo = $periodo_inicio ?: $periodo_fim;
		}

		return $periodo;
	}

}
