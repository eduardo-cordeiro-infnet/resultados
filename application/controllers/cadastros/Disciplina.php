<?php
class Disciplina extends CI_Controller {

		public function __construct()
		{
				parent::__construct();

				$this->load->database();
				$this->load->helper('url');

				$this->load->library('grocery_CRUD');
		}

		public function cadastro()
		{
			$crud = new grocery_CRUD();

			$crud->set_subject('disciplina')
				->set_table('disciplinas')
				->columns('nome', 'id_bloco', 'ativa')
				->fields('nome', 'id_bloco', 'ativa')
				->field_type('ativa', 'dropdown', array('Não', 'Sim'))
				->required_fields('nome')
				->set_relation('id_bloco', 'blocos', '{nome}')
				->display_as('id_bloco', 'Bloco');

			$crud->unset_jquery();
			$output = $crud->render();

			$this->_output_padrao($output);
		}

		function _output_padrao($output = null)
		{
			$this->load->view('templates/cabecalho', $output);
			$this->load->view('templates/padrao', $output);
			$this->load->view('templates/rodape');
		}
}