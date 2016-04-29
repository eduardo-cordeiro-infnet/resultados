<?php
class Modalidade extends CI_Controller {

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

		$crud->set_subject('modalidade')
			->set_table('modalidades')

			->columns('nome', 'ativa')
			->fields('nome', 'ativa')

			->field_type('ativa', 'dropdown', array('Não', 'Sim'))

			->required_fields('nome')

			->unique_fields('nome')

			->unset_read()
		;

		$crud->unset_jquery();
		$output = $crud->render();

		$output->title = 'Cadastro de modalidades';
		$output->mensagem_informativa = 'Nesta tela são cadastradas as modalidades em que são oferecidos os programas do Instituto.</p><p>
			Os registros deste cadastro são utilizados no ' . anchor(site_url('cadastros/classe'), 'cadastro de classes') . ' para definir a modalidade de cada classe.'
		;

		$this->_output_padrao($output);
	}

}
