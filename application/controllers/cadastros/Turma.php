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
			->set_relation_n_n('disciplinas', 'disciplinas_turmas', 'disciplinas', 'id_turma', 'id_disciplina', 'nome')

			->display_as('id_escola_red', 'Escola')
			->display_as('id_formacao', 'Formação')
			->display_as('id_modalidade', 'Modalidade')
			->display_as('qtd_disciplinas_calc', 'Disciplinas')
			->display_as('id_mdl_course_category', 'Categoria no Moodle')
			->display_as('link_moodle', 'Acessar Moodle')

			->add_action('Cadastrar disciplinas da turma', base_url('assets/img/lista_mais.png'), 'cadastros/turma/disciplinas/')

			->callback_column('link_moodle', array($this->Turma_model, 'obter_link_moodle'));

		$crud->unset_jquery();
		$output = $crud->render();

		$output->title = 'Cadastro de turmas';

		$this->_output_padrao($output);
	}

	public function disciplinas($id_turma)
	{
		$crud = new grocery_CRUD();

		$crud->set_model('cadastros/Turma_model');

		$crud->set_subject('disciplina')
			->set_table('disciplinas_turmas')

			->columns('id_bloco_red', 'id_disciplina', 'link_moodle', 'periodo')
			->fields('id_turma', 'id_disciplina', 'id_mdl_course', 'trimestre_inicio', 'ano_inicio', 'trimestre_fim', 'ano_fim')
			->unset_edit_fields('id_turma')

			->field_type('id_turma', 'hidden', $id_turma)
			->field_type('id_mdl_course', 'dropdown', $this->Turma_model->obter_cursos_moodle())
			->field_type('trimestre_inicio', 'dropdown', array(1 => '1T', 2 => '2T', 3 => '3T', 4 => '4T'))
			->field_type('ano_inicio', 'enum', array(2014, 2015, 2016, 2017, 2018))
			->field_type('trimestre_fim', 'dropdown', array(1 => '1T', 2 => '2T', 3 => '3T', 4 => '4T'))
			->field_type('ano_fim', 'enum', array(2014, 2015, 2016, 2017, 2018))

			->required_fields('id_disciplina')

			->set_relation('id_bloco_red', 'blocos', '{nome}')
			->set_relation('id_disciplina', 'disciplinas', '{nome}')

			->display_as('id_bloco_red', 'Bloco')
			->display_as('id_disciplina', 'Disciplina')
			->display_as('link_moodle', 'Acessar Moodle')
			->display_as('periodo', 'Período')
			->display_as('id_mdl_course', 'Curso no Moodle')

			->callback_column('link_moodle', array($this->Turma_model, 'obter_link_disciplina_moodle'))
			->callback_column('periodo', array($this->Turma_model, 'obter_periodo_disciplina'))

			->where('id_turma', $id_turma);

		$crud->unset_jquery();
		$output = $crud->render();

		$output->title = 'Disciplinas da turma';

		$this->_output_padrao($output);
	}

}