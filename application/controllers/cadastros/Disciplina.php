<?php
class Disciplina extends CI_Controller {

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

		$crud->set_subject('disciplina')
			->set_table('disciplinas')

			->columns('id_bloco', 'nome', 'denominacao_bloco', 'ativa')
			->fields('id_bloco', 'nome', 'denominacao_bloco', 'ativa')

			->set_relation('id_bloco', 'blocos', '{nome}')

			->field_type('ativa', 'dropdown', array('Não', 'Sim'))
			->field_type('denominacao_bloco', 'enum', array('DR1', 'DR2', 'DR3', 'DR4', 'Projeto de bloco'))

			->required_fields('nome')

			->display_as('id_bloco', 'Bloco')
			->display_as('denominacao_bloco', 'Denominação no bloco')

			->unset_read()
		;

		$crud->unset_jquery();

		$output = $crud->render();

		$output->title = 'Cadastro de disciplinas';
		$output->mensagem_informativa = 'Nesta tela são cadastradas as disciplinas de cada bloco.</p><p>
			Cada disciplina tem uma denominação no bloco, podendo ser uma das 4 disciplina regulares (DR1, 2, 3 e 4) ou a disciplina do projeto de bloco.</p><p>
			Os registros deste cadastro são utilizados no ' . anchor(site_url('cadastros/turma'), 'cadastro de turmas') . ' para definir as disciplinas de cada turma.'
		;

		$this->_output_padrao($output);
	}

}
