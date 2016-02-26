<?php
class Escola_model extends CI_Model {

        public function __construct()
        {
                $this->load->database();
        }

        public function get_escola($sigla = FALSE)
        {
                if ($sigla === FALSE)
                {
                        $query = $this->db->get('escolas');
                        return $query->result_array();
                }

                $query = $this->db->get_where('escolas', array('sigla' => $sigla));
                return $query->row_array();
        }

        public function get_escola_por_id($id)
        {
                $query = $this->db->get_where('escolas', array('id' => $id));
                return $query->row_array();
        }

        public function set_escola()
        {
            $this->load->helper('url');

            $data = array(
                'nome' => $this->input->post('nome'),
                'sigla' => $this->input->post('sigla')
            );

            return $this->db->insert('escolas', $data);
        }

}