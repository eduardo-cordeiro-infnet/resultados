<?php
class Programa extends CI_Controller {

	public function __construct()
	{
			parent::__construct();

			$this->load
				->library('grocery_CRUD')
				->database()
			;
	}

	function _output_padrao($output = null)
	{
		$this->load->view('templates/cabecalho', $output);
		$this->load->view('templates/padrao', $output);
		$this->load->view('templates/rodape', $output);
	}

	public function cadastro()
	{
		$crud = new grocery_CRUD();

		$crud->set_subject('programa')
			->set_table('programas')

			->columns('id_escola', 'nome', 'sigla', 'ativa')
			->fields('id_escola', 'nome', 'sigla', 'ativa')

			->set_relation('id_escola','escolas','{sigla} ({nome})')

			->field_type('ativa', 'dropdown', array('Não', 'Sim'))

			->required_fields('id_escola', 'nome', 'sigla')

			->unique_fields('sigla')

			->display_as('id_escola','Escola')

			->unset_read()
		;

		$crud->unset_jquery();
		$output = $crud->render();

		$output->title = 'Cadastro de escolas';
		$output->mensagem_informativa = 'Nesta tela são cadastrados os programas do Instituto, independente de serem oferecidas de forma presencial, à distância ou em qualquer modalidade.</p><p>
			Os registros deste cadastro são utilizados no ' . anchor(site_url('cadastros/bloco'), 'cadastro de blocos') . ' para associar blocos a cada programa.'
		;

		$this->_output_padrao($output);
	}

}
