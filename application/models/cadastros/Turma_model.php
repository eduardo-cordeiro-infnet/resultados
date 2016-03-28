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
		$this->load->library('Consultas_SQL');
		$consulta = $this->db->query($this->consultas_sql->mdl_categorias_com_caminho());

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
		$this->load->library('Consultas_SQL');
		$consulta = $this->db->query($this->consultas_sql->mdl_curso_com_caminho_mdl_categoria(), array($id_turma));

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
	 * Nos estados "add" e "edit", não retorna disciplinas já associadas, para impedir duplicidade
	 * Disciplinas de blocos que estejam associados à turma são exibidas nas primeiras opções
	 * @return array
	 */
	public function obter_disciplinas_blocos($id_turma = 0, $state = null, $state_info = null)
	{
		$id_disciplina_turma = (isset($state_info->primary_key)) ? $state_info->primary_key : 0;

		$this->load->library('Consultas_SQL');
		$consulta = $this->db->query($this->consultas_sql->disciplinas_blocos_turma(in_array($state, array('add', 'edit'))), array($id_turma, $id_turma, $id_disciplina_turma));

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
	 * @return string
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

	/**
	 * Obter ID de curso no Moodle
	 *
	 * Retorna o id do curso no Moodle associado à disciplina de turma informada
	 * @return string
	 */
	public function obter_id_curso_moodle($id_disciplina_turma = null)
	{
		$resultado = $this->db->select('id_mdl_course')->get_where('disciplinas_turmas', array('id' => $id_disciplina_turma))->result();

		return isset($resultado[0]) ? $resultado[0]->id_mdl_course : null;
	}

	/**
	 * Obter links de avaliações no Moodle
	 *
	 * Retorna HTML de links para as avaliações da turma no LMS com o ícone do Moodle
	 * @return string
	 */
	public function obter_links_avaliacoes_moodle($valor, $linha)
	{
		$this->load->library('Consultas_SQL');
		$consulta = $this->db->query($this->consultas_sql->mdl_modulos_avaliacoes(), array($linha->id));

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
}
