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
		$data['css_files'] = array(
			base_url('assets/grocery_crud/themes/struct/css/struct.css'),
			base_url('assets/css/relatorio_turma.css')
		);
		$data['js_files'] = array(
			base_url('assets/js/vendor/wholly.js'),
			base_url('assets/js/vendor/jquery.battatech.excelexport.min.js')
		);

		//$this->load->view('templates/cabecalho', $data);
		$this->load->view('templates/cabecalho', $data);
		$this->load->view('templates/menu', $data);
		$this->load->view('templates/navbar', $data);
		//$this->load->view('templates/padrao', $data);
		$this->load->view('templates/relatorios', $data);

		if (isset($data['avaliacoes']))
		{
			$data['fechamento_body'] = "
	<script>
	$(function() {
		RelatorioTurma.formatarTabela();
		$('.link-exportar-excel').click(RelatorioTurma.exportarExcel);
	});
	</script>
			";
			$this->load->view('pages/relatorios/resultados_turma', $data);
		}
		else if (isset($data['turmas']))
		{
			$this->load->view('pages/relatorios/selecionar_turma', $data);
		}

		$this->load->view('templates/rodape', $data);
	}

	public function selecionar_turma()
	{
		$this->load->helper('form');

		$data['turmas'] = $this->Resultados_turma_model->obter_turmas_com_resultados();

		$this->_output_padrao($data);
	}

	public function relatorio($id_turma = null)
	{
		if (!$id_turma)
		{
			$uri_ultimo_segmento = ($this->input->post('id_turma')) ? $this->input->post('id_turma') : 'selecionar_turma';
			redirect('relatorios/resultados_turma/' . $uri_ultimo_segmento);
			die();
		}
		else
		{
			$this->_output_padrao($this->Resultados_turma_model->obter_dados_relatorio($id_turma));
		}
	}

}
