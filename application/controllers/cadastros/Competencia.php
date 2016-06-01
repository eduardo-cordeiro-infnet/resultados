<?php
class Competencia extends CI_Controller {

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
		$this->load->view('templates/menu', $output);
		$this->load->view('templates/navbar', $output);
		$this->load->view('templates/cadastros', $output);
		$this->load->view('templates/rodape', $output);
	}

	public function cadastro($id_turma = null)
	{
		$crud = new grocery_CRUD();
		$crud->set_model('grocery_crud/Competencia_crud_model');

		$crud->set_subject('competência')
			->set_table('competencias')

			->columns('id_turma', 'codigo', 'nome')
			->fields('id_turma', 'codigo', 'nome')

			->field_type('id_turma', 'dropdown', $this->Competencia_crud_model->obter_turmas($id_turma))
			->field_type('ativa', 'dropdown', array('Não', 'Sim'))

			->required_fields('id_turma', 'nome')

			->set_rules('codigo','código','integer|is_natural')

			->display_as('id_turma', 'Turma')
			->display_as('codigo', 'Código')

			->callback_column('id_turma', array($this->Competencia_crud_model, 'obter_coluna_turma'))

			->add_action('Cadastrar subcompetências', base_url('assets/img/ic_comp_add_black_24px.svg'), '', '', array($this->Competencia_crud_model, 'obter_caminho_subcompetencias'))

			->unset_read()
		;

		if (intval($id_turma) > 0)
		{
			$crud->where('id_turma', $id_turma);
		}

		$crud->unset_jquery();
		$output = $crud->render();

		$output->title = 'Cadastro de competências';
		$output->mensagem_informativa = 'Nesta tela são cadastradas as competências de cada disciplina.</p><p>
			O conjunto de competências é específico para a disciplina realizada em cada classe, conforme a associação realizada no ' . anchor(site_url('cadastros/classe'), 'cadastro de classes') . '.</p><p>
			É possível cadastrar as subcompetências clicando no ícone "<i>Cadastrar subcompetências</i>", na coluna "Ações": ' . img(base_url('assets/img/lista-num-decimal.png'), '', array('title' => 'Cadastrar subcompetências'))
		;

		$this->_output_padrao($output);
	}

	public function subcompetencias($id_turma = null)
	{
		$crud = new grocery_CRUD();

		$crud->set_model('grocery_crud/Competencia_crud_model');

		$crud->set_subject('subcompetência')
			->set_table('subcompetencias')

			->columns('id_competencia', 'codigo_completo_calc', 'nome', 'ativa')
			->fields('id_competencia', 'codigo', 'nome', 'obrigatoria', 'ativa')

			->field_type('id_competencia', 'dropdown', $this->Competencia_crud_model->obter_competencias_turma($id_turma))
			->field_type('obrigatoria', 'dropdown', array('Não', 'Sim'))
			->field_type('ativa', 'dropdown', array('Não', 'Sim'))

			->required_fields('id_competencia', 'codigo', 'nome')

			->set_rules('codigo','código','integer|is_natural')

			->display_as('id_competencia', 'Competência')
			->display_as('codigo_completo_calc', 'Código')
			->display_as('obrigatoria', 'Obrigatória')

			->unset_read()
		;

		if (intval($id_turma) > 0)
		{
			$crud->where('id_turma_red', $id_turma);
		}

		$crud->unset_jquery();
		$output = $crud->render();

		$output->title = 'Cadastro de subcompetências';
		$output->mensagem_informativa = 'Nesta tela são cadastradas as subcompetências de cada competência.</p><p>
			O código da subcompetência deve ser cadastrado como número inteiro. Por exemplo, se a subcompetência for 3.4, o código será 4, dentro da competência 3.</p><p>
			Os registros deste cadastro são utilizados no ' . anchor(site_url('cadastros/classe/rubricas'), 'cadastro de subcompetências por rubrica') . '.'
		;
		$this->_output_padrao($output);
	}
}
