<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('obj_array_search_id'))
{
	/**
	 * Procurar ID em array de objetos
	 *
	 * Retorna o primeiro elemento do array que tiver o ID informado
	 * ou null se não houver nenhum
	 * @return stdClass
	 */
	function obj_array_search_id($array, $id)
	{
		foreach($array as $obj)
		{
			if ($id == obj_id($obj))
			{
				return $obj;
			}
		}

		return null;
	}
}

if ( ! function_exists('obj_array_search_prop'))
{
	/**
	 * Procurar propriedade em array de objetos
	 *
	 * Retorna um array com todos os elementos que tiverem a propriedade informada
	 * @return array
	 */
	function obj_array_search_prop($array, $prop, $val)
	{
		$retorno = array();

		foreach($array as $obj)
		{
			if (
				(isset($obj->$prop) && $val == obj_prop_val($obj, $prop))
				|| (isset($obj[$prop]) && $val == $obj[$prop])
			)
			{
				$retorno[] = $obj;
			}
		}

		return $retorno;
	}
}

if ( ! function_exists('obj_array_map_id'))
{
	/**
	 * Mapear ID em array
	 *
	 * Retorna um array com os IDs de todos os objetos de um array
	 * @return array
	 */
	function obj_array_map_id($array)
	{
		return obj_array_map_prop($array, 'id');
	}
}

if ( ! function_exists('obj_array_map_prop'))
{
	/**
	 * Mapear propriedade em array
	 *
	 * Retorna um array com a propriedade informada de todos os objetos de um array
	 * @return array
	 */
	function obj_array_map_prop($array, $prop)
	{
		$retorno = array();

		foreach ($array as $obj)
		{
			if (isset($obj->$prop))
			{
				$retorno[] = obj_prop_val($obj, $prop);
			}
			else if (isset($obj[$prop]))
			{
				$retorno[] = $obj[$prop];
			}
		}

		return $retorno;
	}
}

if ( ! function_exists('obj_prop_val'))
{
	/**
	 * Valor de propriedade de objeto
	 *
	 * Retorna o valor de uma propriedade de um objeto ou null se não existir
	 * Função auxiliar para usar com obj_array_map e outras funções de array
	 * @return mixed
	 */
	function obj_prop_val($obj, $prop)
	{
		return ((isset($obj->$prop)) ? $obj->$prop : null);
	}
}

if ( ! function_exists('obj_id'))
{
	/**
	 * ID de objeto
	 *
	 * Retorna o ID de um objeto
	 * Função auxiliar para usar com array_map e outras funções de array
	 * @return mixed
	 */
	function obj_id($objeto)
	{
		return obj_prop_val($objeto, 'id');
	}
}
