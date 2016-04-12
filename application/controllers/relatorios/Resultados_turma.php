<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Resultados_turma extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load
			->database()
			->model('relatorios/Resultados_turma_model')
		;
	}

	private function _output_padrao($data = null)
	{
		$data['title'] = 'Resultados de competências por turma';
		$data['css_files'] = array(base_url('assets/grocery_crud/themes/flexigrid/css/flexigrid.css'));

		$this->load->view('templates/cabecalho', $data);
		$this->load->view('templates/padrao', $data);

		if (isset($data['avaliacoes']))
		{
			$this->load->view('pages/relatorios/resultados_turma', $data);
		}
		else if (isset($data['disciplinas_turmas']))
		{
			$this->load->view('pages/relatorios/selecionar_turma', $data);
		}

		$this->load->view('templates/rodape', $data);
	}

	public function selecionar_turma()
	{
		$this->load->helper('form');

		$data['disciplinas_turmas'] = $this->Resultados_turma_model->obter_disciplinas_turmas_com_resultados();

		$this->_output_padrao($data);
	}

	public function relatorio($id_disciplina_turma = null)
	{
		if (!$id_disciplina_turma)
		{
			$uri_ultimo_segmento = ($this->input->post('id_disciplina_turma')) ? $this->input->post('id_disciplina_turma') : 'selecionar_turma';
			redirect('relatorios/resultados_turma/' . $uri_ultimo_segmento);
			die();
		}
		else
		{
			$this->load->helper('date');

			$this->_output_padrao($this->Resultados_turma_model->obter_dados_relatorio($id_disciplina_turma));
		}
	}
}
