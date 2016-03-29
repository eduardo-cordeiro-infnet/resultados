<?php
class Turma extends CI_Controller {

	public $escolas = array();

	public function __construct()
	{
			parent::__construct();

			$this->load->database();

			$this->load->library('grocery_CRUD');
	}

	function _output_padrao($output = null)
	{
		$this->load->view('templates/cabecalho', $output);
		$this->load->view('templates/padrao', $output);
		$this->load->view('templates/rodape');
	}

	public function cadastro()
	{
		$crud = new grocery_CRUD();

		$crud->set_model('cadastros/Turma_model');

		$crud->set_subject('turma')
			->set_table('turmas')

			->columns('id_escola_red', 'id_formacao', 'nome', 'id_modalidade', 'qtd_disciplinas_calc', 'ativa', 'link_moodle')
			->fields('nome', 'id_formacao', 'id_modalidade', 'id_mdl_course_category', 'ativa', 'disciplinas')

			->field_type('ativa', 'dropdown', array('Não', 'Sim'))
			->field_type('id_mdl_course_category', 'dropdown', $this->Turma_model->obter_categorias_moodle())

			->required_fields('nome', 'id_formacao', 'id_modalidade')

			->set_relation('id_escola_red', 'escolas', '{sigla}')
			->set_relation('id_formacao', 'formacoes', '{sigla} - {nome}')
			->set_relation('id_modalidade', 'modalidades', '{nome}')
			->set_relation_n_n(
				'disciplinas',
				'disciplinas_turmas',
				'disciplinas',
				'id_turma',
				'id_disciplina',
				'nome'
			)

			->display_as('id_escola_red', 'Escola')
			->display_as('id_formacao', 'Formação')
			->display_as('id_modalidade', 'Modalidade')
			->display_as('qtd_disciplinas_calc', 'Disciplinas')
			->display_as('id_mdl_course_category', 'Categoria no Moodle')
			->display_as('link_moodle', 'Acessar Moodle')

			->callback_column('link_moodle', array($this->Turma_model, 'obter_link_moodle'))

			->add_action('Cadastrar disciplinas da turma', base_url('assets/img/livros-vertical.png'), 'cadastros/turma/disciplinas');

		$crud->unset_jquery();
		$output = $crud->render();

		$output->title = 'Cadastro de turmas';

		$this->_output_padrao($output);
	}

	public function disciplinas($id_turma = null)
	{
		$crud = new grocery_CRUD();

		$crud->set_model('cadastros/Turma_model');

		$crud->set_subject('disciplina')
			->set_table('disciplinas_turmas')

			->columns('id_bloco_red', 'id_disciplina', 'link_moodle', 'periodo')
			->fields('id_turma', 'id_disciplina', 'id_mdl_course', 'trimestre_inicio', 'ano_inicio', 'trimestre_fim', 'ano_fim')
			->unset_edit_fields('id_turma')

			->set_relation('id_bloco_red', 'blocos', '{nome}')

			->field_type('id_turma', 'hidden', $id_turma)
			->field_type('id_disciplina', 'dropdown', $this->Turma_model->obter_disciplinas_blocos($id_turma, $crud->getState(), $crud->getStateInfo()))
			->field_type('id_mdl_course', 'dropdown', $this->Turma_model->obter_cursos_moodle($id_turma))
			->field_type('trimestre_inicio', 'dropdown', array(1 => '1T', 2 => '2T', 3 => '3T', 4 => '4T'))
			->field_type('ano_inicio', 'enum', array(2014, 2015, 2016, 2017, 2018))
			->field_type('trimestre_fim', 'dropdown', array(1 => '1T', 2 => '2T', 3 => '3T', 4 => '4T'))
			->field_type('ano_fim', 'enum', array(2014, 2015, 2016, 2017, 2018))

			->callback_column('link_moodle', array($this->Turma_model, 'obter_link_disciplina_moodle'))
			->callback_column('periodo', array($this->Turma_model, 'obter_periodo_disciplina'))

			->required_fields('id_disciplina')

			->display_as('id_bloco_red', 'Bloco')
			->display_as('id_disciplina', 'Disciplina')
			->display_as('link_moodle', 'Acessar Moodle')
			->display_as('periodo', 'Período')
			->display_as('id_mdl_course', 'Disciplina no Moodle')

			->add_action('Cadastrar avaliações', base_url('assets/img/texto-lapis.png'), 'cadastros/turma/avaliacoes')
			->add_action('Cadastrar competências', base_url('assets/img/lista-num.png'), 'cadastros/competencia')
		;

		if (intval($id_turma) > 0)
		{
			$crud->where('id_turma', $id_turma);
		}

		$crud->unset_jquery();
		$output = $crud->render();

		$output->title = 'Cadastro de disciplinas da turma';

		$this->_output_padrao($output);
	}

	public function avaliacoes($id_disciplina_turma = null)
	{
		$crud = new grocery_CRUD();

		$crud->set_model('cadastros/Turma_model');

		$crud->set_subject('avaliação')
			->set_table('avaliacoes')

			->columns('id_disciplina_turma', 'nome', 'links_moodle', 'ativa')
			->fields('id_disciplina_turma', 'nome', 'ativa', 'atividades_moodle')

			->set_relation_n_n(
				'atividades_moodle',
				'avaliacoes_mdl_course_modules',
				'lmsinfne_mdl.mdl_assign',
				'id_avaliacao',
				'instance_mdl_course_modules',
				'name',
				'',
				array('course' => $this->Turma_model->obter_id_curso_moodle($id_disciplina_turma))
			)

			->field_type('id_disciplina_turma', 'dropdown', $this->Turma_model->obter_disciplinas_turmas($id_disciplina_turma))
			->field_type('ativa', 'dropdown', array('Não', 'Sim'))

			->callback_column('links_moodle', array($this->Turma_model, 'obter_links_avaliacoes_moodle'))

			->required_fields('id_disciplina_turma', 'nome')

			->display_as('id_disciplina_turma', 'Disciplina')
			->display_as('links_moodle', 'Acessar Moodle')
			->display_as('atividades_moodle', 'Atividades no Moodle')

			->add_action('Associar competências a rubricas', base_url('assets/img/prancheta-correto.png'), 'cadastros/turma/rubricas')
		;

		if (intval($id_disciplina_turma) > 0)
		{
			$crud->where('id_disciplina_turma', $id_disciplina_turma);
		}

		$crud->unset_jquery();
		$output = $crud->render();


		$output->title = 'Cadastro de avaliações da disciplina';

		$this->_output_padrao($output);
	}

	public function rubricas($id_avaliacao = null)
	{
		$crud = new grocery_CRUD();

		$crud->set_model('cadastros/Turma_model');

		$id_disciplina_turma = $this->Turma_model->obter_id_disciplina_turma($id_avaliacao);

		$crud->set_subject('rubrica')
			->set_table('v_rubricas_avaliacoes')

			->set_primary_key('id_mdl_gradingform_rubric_criteria')

			->columns('id_disciplina_turma', 'id_avaliacao', 'rubrica', 'subcompetencias')
			->fields('id_mdl_gradingform_rubric_criteria', 'id_disciplina_turma', 'id_avaliacao', 'rubrica', 'subcompetencias')

			->set_relation_n_n(
				'subcompetencias',
				'subcompetencias_mdl_gradingform_rubric_criteria',
				'subcompetencias',
				'id_mdl_gradingform_rubric_criteria',
				'id_subcompetencia',
				'{codigo_competencia_red}.{codigo} {nome}',
				'',
				array('id_disciplina_turma_red' => $id_disciplina_turma)
			)

			# Campo chave incluído para haver algum campo na operação update
			->field_type('id_mdl_gradingform_rubric_criteria', 'hidden')
			->field_type('rubrica', 'readonly')

			->callback_column('id_disciplina_turma', array($this->Turma_model, 'obter_disciplina_turma'))
			->callback_edit_field('id_disciplina_turma', array($this->Turma_model, 'obter_disciplina_turma'))
			->callback_column('id_avaliacao', array($this->Turma_model, 'obter_nome_avaliacao'))
			->callback_edit_field('id_avaliacao', array($this->Turma_model, 'obter_nome_avaliacao'))

			->display_as('id_disciplina_turma', 'Disciplina')
			->display_as('id_avaliacao', 'Avaliação')
			->display_as('subcompetencias', 'Subcompetências')

			->unset_add()
			->unset_delete()
		;

		if (intval($id_avaliacao) > 0)
		{
			$crud->where('id_avaliacao', $id_avaliacao);
		}

		$crud->unset_jquery();
		$output = $crud->render();

		$output->title = 'Cadastro de avaliações da disciplina';

		$this->_output_padrao($output);
	}
}
