<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Classe_model extends CI_Model {
	public $id;
	public $nome;
	public $programa;
	public $modalidade;
	public $escola;
	public $id_mdl_course_category;
	public $trimestre;
	public $ano;
	public $id_mdl_course;

	public function __construct($param = null)
	{
		if (is_array($param))
		{
			if (isset($param['id']))
			{
				$this->id = $param['id'];
			}
			if (isset($param['nome']))
			{
				$this->nome = $param['nome'];
			}
			if (isset($param['programa']))
			{
				$this->programa = $param['programa'];
			}
			if (isset($param['modalidade']))
			{
				$this->modalidade = $param['modalidade'];
			}
			if (isset($param['escola']))
			{
				$this->escola = $param['escola'];
			}
			if (isset($param['id_mdl_course_category']))
			{
				$this->id_mdl_course_category = $param['id_mdl_course_category'];
			}
			if (isset($param['trimestre']))
			{
				$this->trimestre = $param['trimestre'];
			}
			if (isset($param['ano']))
			{
				$this->ano = $param['ano'];
			}
			if (isset($param['id_mdl_course']))
			{
				$this->id_mdl_course = $param['id_mdl_course'];
			}
		}

		$this->load->database();
	}
}
