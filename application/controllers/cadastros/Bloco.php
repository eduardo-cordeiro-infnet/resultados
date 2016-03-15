<?php
class Bloco extends CI_Controller {

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

			$crud->set_subject('bloco')
				->set_table('blocos')
				->columns('nome', 'ativo')
				->fields('nome', 'ativo')
				->field_type('ativo', 'dropdown', array('Não', 'Sim'))
				->required_fields('nome')
				->unique_fields('nome');

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