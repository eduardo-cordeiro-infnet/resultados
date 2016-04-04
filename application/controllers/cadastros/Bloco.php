<?php
class Bloco extends CI_Controller {

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

		$crud->set_subject('bloco')
			->set_table('blocos')

			->columns('nome', 'ativo')
			->fields('nome', 'ativo')

			->field_type('ativo', 'dropdown', array('Não', 'Sim'))

			->required_fields('nome')

			->unique_fields('nome')

			->unset_read()
		;

		$crud->unset_jquery();
		$output = $crud->render();

		$output->title = 'Cadastro de blocos';
		$output->mensagem_informativa = 'Nesta tela são cadastrados os blocos de disciplinas. Blocos são agrupamentos de 5 disciplinas (4 regulares + projeto de bloco) com um assunto em comum.</p><p>
			Um mesmo bloco pode ser utilizado em diferentes formações, contanto que as disciplinas do bloco sejam as mesmas.</p><p>
			Os registros deste cadastro são utilizados no ' . anchor(site_url('cadastros/disciplina'), 'cadastro de disciplinas') . ' para definir a o bloco de cada disciplina.'
		;

		$this->_output_padrao($output);
	}

}
