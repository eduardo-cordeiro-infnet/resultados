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

			$crud->set_subject('formação')
				->set_table('formacoes')
				->columns('id_escola', 'nome', 'sigla', 'ativa')
				->fields('id_escola', 'nome', 'sigla', 'ativa')
				->field_type('ativa', 'dropdown', array('Não', 'Sim'))
				->required_fields('id_escola', 'nome', 'sigla')
				->unique_fields('sigla')
				->set_relation('id_escola','escolas','{sigla} ({nome})')
				->display_as('id_escola','Escola');

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