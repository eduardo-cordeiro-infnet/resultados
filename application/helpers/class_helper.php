<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('carregar_classe'))
{
	function carregar_classe()
	{
		foreach (func_get_args() as $param) {
			if (is_array($param))
			{
				foreach ($param as $param_item) {
					carregar_classe($param_item);
				}
			}
			else
			{
				require_once APPPATH . str_replace('\\', DIRECTORY_SEPARATOR, $param) . '.php';
			}
		};
	}
}
