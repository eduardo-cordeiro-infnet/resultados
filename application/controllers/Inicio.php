<?php
class Inicio extends CI_Controller {

		public function __construct()
		{
				parent::__construct();
		}

		public function index()
		{
			$data['css_files'] = array();
			$data['js_files'] = array();
			$data['output'] = '';

			$data['title'] = 'Cadastros';

			$this->load->view('templates/cabecalho', $data);
			$this->load->view('templates/padrao', $data);
			$this->load->view('templates/rodape');
		}
}
