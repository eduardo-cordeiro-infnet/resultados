<?php
class Turma extends CI_Controller {

	public $escolas = array();

	public function __construct()
	{
			parent::__construct();

			$this->load->database();

			$this->load->library('grocery_CRUD');
	}

	public function cadastro()
	{
		$crud = new grocery_CRUD();

		$crud->set_model('cadastros/Turma_model');

		$crud->set_subject('turma')
			->set_table('turmas')

			->columns('id_escola_red', 'id_formacao', 'nome', 'id_modalidade', 'ativa')
			->fields('nome', 'id_formacao', 'id_modalidade', 'id_mdl_course_category', 'ativa')

			->field_type('ativa', 'dropdown', array('Não', 'Sim'))
			->field_type('id_mdl_course_category', 'dropdown', $this->Turma_model->obter_categorias_moodle())

			->required_fields('nome', 'id_formacao', 'id_modalidade')

			->set_relation('id_escola_red', 'escolas', '{sigla}')
			->set_relation('id_formacao', 'formacoes', '{sigla} - {nome}')
			->set_relation('id_modalidade', 'modalidades', '{nome}')

			->display_as('id_escola_red', 'Escola')
			->display_as('id_formacao', 'Formação')
			->display_as('id_modalidade', 'Modalidade')
			->display_as('id_mdl_course_category', 'Categoria no Moodle')

			->callback_column('nome', array($this->Turma_model, 'obter_nome_com_link_moodle'));

		$crud->unset_jquery();
		$output = $crud->render();

		$output->title = 'Cadastro de turmas';

		$this->_output_padrao($output);
	}

	function _output_padrao($output = null)
	{
		$this->load->view('templates/cabecalho', $output);
		$this->load->view('templates/padrao', $output);
		$this->load->view('templates/rodape');
	}
}