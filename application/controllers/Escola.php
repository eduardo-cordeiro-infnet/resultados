<?php
class Escola extends CI_Controller {

        public function __construct()
        {
                parent::__construct();
                $this->load->model('escola_model');
                $this->load->helper('url_helper');
        }

        public function index()
        {
                $data['escolas'] = $this->escola_model->get_escola();
                $data['title'] = 'Cadastro de escolas';

                $this->load->view('templates/header', $data);
                $this->load->view('escola/index', $data);
                $this->load->view('templates/footer');
        }

        public function exibir($sigla = NULL)
        {
                $data['escola_item'] = $this->escola_model->get_escola($sigla);

                if (empty($data['escola_item']))
                {
                        show_404();
                }

                $data['title'] = $data['escola_item']['nome'];

                $this->load->view('templates/header', $data);
                $this->load->view('escola/exibir', $data);
                $this->load->view('templates/footer');
        }

        public function exibir_por_id($id)
        {
                $data['escola_item'] = $this->escola_model->get_escola_por_id($id);

                if (empty($data['escola_item']))
                {
                        show_404();
                }

                $data['title'] = $data['escola_item']['nome'];

                $this->load->view('templates/header', $data);
                $this->load->view('escola/exibir', $data);
                $this->load->view('templates/footer');
        }

        public function cadastrar()
        {
            $this->load->helper('form');
            $this->load->library('form_validation');

            $data['title'] = 'Cadastrar';

            $this->form_validation->set_rules('nome', 'Nome', 'required');
            $this->form_validation->set_rules('sigla', 'Sigla', 'required');

            if ($this->form_validation->run() === FALSE)
            {
                $this->load->view('templates/header', $data);
                $this->load->view('escola/cadastrar');
                $this->load->view('templates/footer');

            }
            else
            {
                $this->escola_model->set_escola();
                $this->load->view('escola/sucesso');
            }
        }
}