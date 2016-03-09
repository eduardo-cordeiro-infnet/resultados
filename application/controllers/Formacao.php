<?php
class Formacao extends CI_Controller {

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

			$crud->set_subject('formação');
			$crud->set_table('formacoes');
			$crud->fields('nome', 'sigla', 'id_escola');
			$crud->display_as('id_escola','Escola');
			$crud->required_fields('nome', 'sigla', 'id_escola');
			$crud->set_relation('id_escola','escolas','{sigla} ({nome})');

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