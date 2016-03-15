<?php
class Escola extends CI_Controller {

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

			$crud->set_subject('escola')
				->set_table('escolas')
				->columns('nome', 'sigla', 'ativa')
				->fields('nome', 'sigla', 'ativa')
				->field_type('ativa', 'dropdown', array('Não', 'Sim'))
				->required_fields('nome', 'sigla', 'ativa')
				->unique_fields('sigla');

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