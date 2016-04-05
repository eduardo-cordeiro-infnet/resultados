<?php
class Escola extends CI_Controller {

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

		$crud->set_subject('escola')
			->set_table('escolas')

			->columns('nome', 'sigla', 'ativa')
			->fields('nome', 'sigla', 'ativa')

			->field_type('ativa', 'dropdown', array('Não', 'Sim'))

			->required_fields('nome', 'sigla', 'ativa')

			->unique_fields('sigla')

			->unset_read()
		;

		$crud->unset_jquery();
		$output = $crud->render();

		$output->title = 'Cadastro de escolas';
		$output->mensagem_informativa = 'Nesta tela são cadastradas as escolas do Instituto.</p><p>
			Os registros deste cadastro são utilizados no ' . anchor(site_url('cadastros/programa'), 'cadastro de programas') . ' para associar cada curso a uma escola.'
		;

		$this->_output_padrao($output);
	}

}
