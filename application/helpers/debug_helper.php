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

//http://php.net/manual/en/function.debug-backtrace.php#112238
if ( ! function_exists('generate_call_trace'))
{
	function generate_call_trace()
	{
		$e = new Exception();
		$trace = explode("\n", $e->getTraceAsString());
		// reverse array to make steps line up chronologically
		$trace = array_reverse($trace);
		array_shift($trace); // remove {main}
		array_pop($trace); // remove call to this method
		$length = count($trace);
		$result = array();

		for ($i = 0; $i < $length; $i++)
		{
			$result[] = ($i + 1)  . ')' . substr($trace[$i], strpos($trace[$i], ' ')); // replace '#someNum' with '$i)', set the right ordering
		}

		return "\t" . implode("\n\t", $result);
	}
}
