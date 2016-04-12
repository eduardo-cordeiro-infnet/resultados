<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
//http://php.net/manual/en/function.var-dump.php#51119
if ( ! function_exists('var_dump_pre'))
{
	function var_dump_pre($mixed = null) {
		echo '<pre>';
		var_dump($mixed);
		echo '</pre>';
		return null;
	}
}

if ( ! function_exists('var_dump_ret'))
{
	function var_dump_ret($mixed = null) {
		ob_start();
		var_dump($mixed);
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
}
