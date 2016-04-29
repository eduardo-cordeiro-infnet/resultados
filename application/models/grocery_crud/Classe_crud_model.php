<?php
class Classe_crud_model extends Grocery_CRUD_Model {

	/**
	 * Obter link Moodle
	 *
	 * Retorna HTML de um link para a categoria da classe no LMS com o ícone do Moodle
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
						'alt' => 'Acessar classe no Moodle',
						'title' => 'Acessar classe no Moodle',
						'class' => 'tamanho-icone'
					)
				)
			);
		}
	}

	/**
	 * Obter link da turma Moodle
	 *
	 * Retorna HTML de um link para a turma no LMS com o ícone do Moodle
	 * @return string
	 */
	public function obter_link_turma_moodle($valor, $linha)
	{
		if ($linha->id_mdl_course)
		{
			return anchor_popup(
				URL_BASE_LMS . '/course/view.php?id=' . $linha->id_mdl_course,
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
		$consulta = $this->db->query($this->consultas_sql->classes_mdl_categorias_com_caminho());

		$categorias_moodle = array();

		foreach ($consulta->result() as $linha) {
			$categorias_moodle[$linha->id] = $linha->categoria_com_caminho;
		}

		return $categorias_moodle;
	}

	/**
	 * Obter categorias Moodle
	 *
	 * Retorna o caminho completo de todas as categorias do Moodle, ordenadas por hierarquia
	 * @return array
	 */
	public function obter_blocos()
	{
		$consulta = $this->db
			->select('blocos.id, blocos.nome')
			->group_by('blocos.id')
			->get('blocos');

		$resultado = array();

		foreach ($consulta->result() as $lin) {
			$resultado[$lin->id] = $lin->nome;
		}

		return $resultado;
	}

	/**
	 * Obter cursos Moodle
	 *
	 * Retorna o caminho completo de todos os cursos do Moodle, ordenados por hierarquia
	 * Se houver categoria do moodle associada à classe, os cursos dessa classe são ordenados no início
	 * @return array
	 */
	public function obter_cursos_moodle($id_classe = 0)
	{
		$this->load->library('Consultas_SQL');
		$consulta = $this->db->query($this->consultas_sql->mdl_curso_com_caminho_mdl_categoria(), array($id_classe));

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
	 * Disciplinas de blocos que estejam associados à classe são exibidas nas primeiras opções
	 * @return array
	 */
	public function obter_disciplinas_blocos($id_classe = 0, $state = null, $state_info = null)
	{
		$id_turma = (isset($state_info->primary_key)) ? $state_info->primary_key : 0;

		$this->load->library('Consultas_SQL');
		$consulta = $this->db->query($this->consultas_sql->disciplinas_blocos_classe(in_array($state, array('add', 'edit'))), array($id_classe, $id_classe, $id_turma));

		$disciplinas_blocos = array();

		foreach ($consulta->result() as $linha) {
			$disciplinas_blocos[$linha->id] = $linha->disciplina_bloco;
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
	 * Obter período da classe
	 *
	 * Retorna o período da classe formatado
	 * @return string
	 */
	public function obter_periodo_classe($valor, $linha) {
		return implode('T', array_filter(array($linha->trimestre, $linha->ano)));
	}

	/**
	 * Obter período da disciplina
	 *
	 * Retorna o período da disciplina formatado
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
	 * Retorna o ID do curso no Moodle associado à turma informada
	 * @return string
	 */
	public function obter_id_curso_moodle($id_turma = null)
	{
		$resultado = $this->db->select('id_mdl_course')->get_where('turmas', array('id' => $id_turma))->result();

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
		$this->load->helper('class_helper');
		carregar_classe('models/Avaliacao_model');

		$avaliacao = new Avaliacao_model();

		return $avaliacao->obter_links_moodle($linha->id);
	}

	/**
	 * Obter disciplinas de classes
	 *
	 * Retorna uma lista com as disciplinas que estão associadas a classes
	 * @return array
	 */
	public function obter_turmas($id_turma)
	{
		$this->load->library('Consultas_SQL');
		$consulta = $this->db->query($this->consultas_sql->turmas_com_caminho(), array($id_turma));

		$turmas = array();

		foreach ($consulta->result() as $linha)
		{
			$turmas[$linha->id] = $linha->turma_com_caminho;
		}

		return $turmas;
	}

	/**
	 * Obter ID de turma
	 *
	 * Retorna o ID de uma turma, a partir do ID de uma avaliação
	 * @return string
	 */
	public function obter_id_turma($id_avaliacao = null)
	{
		if ($id_avaliacao)
		{
			$resultado = $this->db->select('id_turma')->get_where('avaliacoes', array('id' => $id_avaliacao))->result();

			return isset($resultado[0]) ? $resultado[0]->id_turma : null;
		}

		return null;
	}

	/**
	 * Obter caminho de turma
	 *
	 * Retorna o caminho de uma turma
	 * @return array
	 */
	public function obter_caminho_turma($id_turma)
	{
		$this->load->library('Consultas_SQL');
		$consulta = $this->db->query($this->consultas_sql->turmas_com_caminho(true), array($id_turma));

		foreach ($consulta->result() as $linha) {
			return $linha->turma_com_caminho;
		}

		return null;
	}

	/**
	 * Obter nome de avaliação
	 *
	 * Retorna o nome da avaliação para exibição no cadastro de competências por rubrica
	 * @return string
	 */
	public function obter_nome_avaliacao($id_avaliacao, $id_mdl_gradingform_rubric_criteria)
	{
		if ($id_mdl_gradingform_rubric_criteria instanceof stdClass)
		{
			$id_mdl_gradingform_rubric_criteria = $id_mdl_gradingform_rubric_criteria->id_mdl_gradingform_rubric_criteria;
		}

		$this->load->library('Consultas_SQL');
		$resultado = $this->db->query($this->consultas_sql->nome_avaliacao(), array($id_avaliacao, $id_mdl_gradingform_rubric_criteria))->result();

		return isset($resultado[0]) ? $resultado[0]->nome_avaliacao : null;
	}

	/**
	 * Salvar rubricas e subcompetências
	 *
	 * Baseado em `Grocery_crud_model->db_relation_n_n_update`
	 * Grava a relação n-n de rubricas e subcompetências, sem validar
	 * nem enviar o formulário
	 */
	public function salvar_rubricas_subcompetencias($post_array, $primary_key)
	{
		$field_name = 'subcompetencias';
		$field_info = new stdClass();
		$field_info->primary_key_alias_to_this_table = 'id_mdl_gradingform_rubric_criteria';
		$field_info->primary_key_alias_to_selection_table = 'id_subcompetencia';
		$field_info->relation_table = 'subcompetencias_mdl_gradingform_rubric_criteria';

		$post_data = $post_array[$field_name];

		$this->db->where($field_info->primary_key_alias_to_this_table, $primary_key);
		if(!empty($post_data))
			$this->db->where_not_in($field_info->primary_key_alias_to_selection_table , $post_data);
		$this->db->delete($field_info->relation_table);

		$counter = 0;
		if(!empty($post_data))
		{
			foreach($post_data as $primary_key_value)
			{
				$where_array = array(
					$field_info->primary_key_alias_to_this_table => $primary_key,
					$field_info->primary_key_alias_to_selection_table => $primary_key_value,
				);

				$this->db->where($where_array);
				$count = $this->db->from($field_info->relation_table)->count_all_results();

				if($count == 0)
				{
					if(!empty($field_info->priority_field_relation_table))
						$where_array[$field_info->priority_field_relation_table] = $counter;

					$this->db->insert($field_info->relation_table, $where_array);

				}elseif($count >= 1 && !empty($field_info->priority_field_relation_table))
				{
					$this->db->update( $field_info->relation_table, array($field_info->priority_field_relation_table => $counter) , $where_array);
				}

				$counter++;
			}
		}
	}

}
