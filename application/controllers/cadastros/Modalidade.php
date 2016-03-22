<?php
class Modalidade extends CI_Controller {

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

			$crud->set_subject('modalidade')
				->set_table('modalidades')
				->columns('nome', 'ativa')
				->fields('nome', 'ativa')
				->field_type('ativa', 'dropdown', array('Não', 'Sim'))
				->required_fields('nome')
				->unique_fields('nome');

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