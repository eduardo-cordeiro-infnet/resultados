<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if ( ! function_exists('array_map_params'))
{
	/**
	 * Mapear array com parâmetros
	 *
	 * Executa uma função com cada elemento do array, passando os parâmetros indicados
	 * O argumento $pos_item indica a posição em o que item do array deve ser incluída nos argumentos
	 * @return array
	 */
	function array_map_params($array, $funcao, $params, $pos_item = 0)
	{
		$retorno = array();

		foreach($array as $item)
		{
			$params_chamada = $params;
			array_splice($params_chamada, $pos_item, 0, $item);
			$retorno[] = call_user_func_array($funcao, $params_chamada);
		}

		return $retorno;
	}
}

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
				|| (is_array($obj) && isset($obj[$prop]) && $val == $obj[$prop])
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
	 * Se $manter_indices === true, retornar null para os objetos que não possuírem ID
	 * @return array
	 */
	function obj_array_map_id($array, $manter_indices = false)
	{
		return obj_array_map_prop($array, 'id', $manter_indices);
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
	function obj_array_map_prop($array, $prop, $manter_indices = false)
	{
		$retorno = array();

		foreach ($array as $obj)
		{
			if (isset($obj->$prop))
			{
				$retorno[] = obj_prop_val($obj, $prop);
			}
			else if (is_array($obj) && isset($obj[$prop]))
			{
				$retorno[] = $obj[$prop];
			}
			else
			{
				$retorno[] = null;
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
