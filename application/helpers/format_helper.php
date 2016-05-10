<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('formatar_caminho'))
{
	/**
	 * Formatar caminho
	 *
	 * Formata um string de caminho (de curso Moodle ou registro do sistema)
	 * @return string
	 */
	function formatar_caminho($caminho, $separador = ' > ')
	{
		$itens_caminho = explode($separador, $caminho);
		$item_final = array_pop($itens_caminho);

		return '<span class="bc">' . implode($separador, $itens_caminho) . '</span>' . '<span class="dp">' . $item_final . '</span>';
	}
}
